<?php
// FILE: /app/helpers/RoutingEngine.php

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Zone.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/RoutingRule.php';
require_once __DIR__ . '/../models/RoutingLog.php';

/**
 * RoutingEngine Class
 * Core logic for assigning leads to agents based on routing rules and zones
 */
class RoutingEngine {
    private $leadModel;
    private $zoneModel;
    private $userModel;
    private $ruleModel;
    private $logModel;

    /**
     * Constructor
     */
    public function __construct() {
        $this->leadModel = new Lead();
        $this->zoneModel = new Zone();
        $this->userModel = new User();
        $this->ruleModel = new RoutingRule();
        $this->logModel = new RoutingLog();
    }

    /**
     * Route a lead to an agent
     * @param int $leadId
     * @param int $tenantId
     * @return array Result with agent_id, rule_id, reason
     */
    public function routeLead($leadId, $tenantId) {
        $lead = $this->leadModel->findById($leadId);
        if (!$lead || $lead['tenant_id'] != $tenantId) {
            return ['success' => false, 'message' => 'Lead not found'];
        }

        // 1. Get active routing rules for tenant
        $rules = $this->ruleModel->getActiveRules($tenantId);

        // 2. Find matching rule
        $matchedRule = null;
        foreach ($rules as $rule) {
            if ($this->ruleModel->matchesLead($rule, $lead)) {
                $matchedRule = $rule;
                break;
            }
        }

        // 3. Determine zone from lead location
        $zone = null;
        if ($lead['preferred_city']) {
            $zone = $this->zoneModel->findByLocation(
                $tenantId,
                $lead['preferred_city'],
                $lead['preferred_area']
            );
        }

        // 4. Get candidate agents
        $candidates = $this->getCandidateAgents($tenantId, $matchedRule, $zone, $lead);

        if (empty($candidates)) {
            // No agents available - leave unassigned
            $this->logModel->logDecision([
                'tenant_id' => $tenantId,
                'lead_id' => $leadId,
                'selected_agent_id' => null,
                'rule_id' => $matchedRule ? $matchedRule['id'] : null,
                'zone_id' => $zone ? $zone['id'] : null,
                'reason' => 'No available agents matching criteria',
                'strategy_used' => 'none'
            ]);

            return [
                'success' => false,
                'message' => 'No available agents',
                'agent_id' => null
            ];
        }

        // 5. Select agent based on strategy
        $strategy = $matchedRule ? $matchedRule['assignment_strategy'] : 'least_active';
        $selectedAgent = $this->selectAgent($candidates, $strategy);

        if (!$selectedAgent) {
            return [
                'success' => false,
                'message' => 'Could not select agent',
                'agent_id' => null
            ];
        }

        // 6. Assign lead to agent
        $this->leadModel->assignToAgent(
            $leadId,
            $selectedAgent['id'],
            $matchedRule ? $matchedRule['id'] : null
        );

        // 7. Log decision
        $reason = $this->generateReason($matchedRule, $zone, $strategy);
        $this->logModel->logDecision([
            'tenant_id' => $tenantId,
            'lead_id' => $leadId,
            'selected_agent_id' => $selectedAgent['id'],
            'rule_id' => $matchedRule ? $matchedRule['id'] : null,
            'zone_id' => $zone ? $zone['id'] : null,
            'reason' => $reason,
            'strategy_used' => $strategy
        ]);

        // 8. Send notification email
        require_once __DIR__ . '/Email.php';
        Email::sendLeadAssignment($selectedAgent, $lead);

        return [
            'success' => true,
            'agent_id' => $selectedAgent['id'],
            'agent_name' => $selectedAgent['name'],
            'rule_id' => $matchedRule ? $matchedRule['id'] : null,
            'rule_name' => $matchedRule ? $matchedRule['name'] : null,
            'reason' => $reason,
            'strategy' => $strategy
        ];
    }

    /**
     * Get candidate agents based on rules and zone
     * @param int $tenantId
     * @param array|null $rule
     * @param array|null $zone
     * @param array $lead
     * @return array
     */
    private function getCandidateAgents($tenantId, $rule, $zone, $lead) {
        $candidates = [];

        // If rule specifies target agents
        if ($rule && !empty($rule['target_agent_ids'])) {
            $agentIds = json_decode($rule['target_agent_ids'], true);
            if ($agentIds && is_array($agentIds)) {
                foreach ($agentIds as $agentId) {
                    $agent = $this->userModel->findById($agentId);
                    if ($agent && $agent['is_active'] && $this->userModel->hasCapacity($agentId)) {
                        $candidates[] = $agent;
                    }
                }
            }
        }

        // If zone is identified, get agents assigned to that zone
        if (empty($candidates) && $zone) {
            $zoneAgents = $this->zoneModel->getAgents($zone['id']);
            foreach ($zoneAgents as $agent) {
                if ($this->userModel->hasCapacity($agent['id'])) {
                    $candidates[] = $agent;
                }
            }
        }

        // Fallback: get any available agent from tenant
        if (empty($candidates)) {
            $allAgents = $this->userModel->getAgentsByTenant($tenantId, true);
            foreach ($allAgents as $agent) {
                if ($this->userModel->hasCapacity($agent['id'])) {
                    $candidates[] = $agent;
                }
            }
        }

        return $candidates;
    }

    /**
     * Select agent from candidates based on strategy
     * @param array $candidates
     * @param string $strategy
     * @return array|null
     */
    private function selectAgent($candidates, $strategy) {
        if (empty($candidates)) {
            return null;
        }

        switch ($strategy) {
            case 'round_robin':
                return $this->selectRoundRobin($candidates);

            case 'least_active':
                return $this->selectLeastActive($candidates);

            case 'priority_based':
                return $this->selectByPriority($candidates);

            case 'weighted':
                return $this->selectWeighted($candidates);

            default:
                return $this->selectLeastActive($candidates);
        }
    }

    /**
     * Round robin selection
     * @param array $candidates
     * @return array
     */
    private function selectRoundRobin($candidates) {
        // Simple: return first candidate
        // In production, would track last assigned and rotate
        return $candidates[0];
    }

    /**
     * Select agent with least active leads
     * @param array $candidates
     * @return array
     */
    private function selectLeastActive($candidates) {
        $leastActive = null;
        $minActiveLeads = PHP_INT_MAX;

        foreach ($candidates as $candidate) {
            $sql = "SELECT COUNT(*) as active_count
                    FROM leads
                    WHERE assigned_agent_id = ? AND status IN ('new', 'assigned', 'contacted', 'qualified')";

            $db = Database::getInstance();
            $stmt = $db->query($sql, [$candidate['id']]);
            $result = $stmt->fetch();

            if ($result['active_count'] < $minActiveLeads) {
                $minActiveLeads = $result['active_count'];
                $leastActive = $candidate;
            }
        }

        return $leastActive ?: $candidates[0];
    }

    /**
     * Select by priority (highest priority first)
     * @param array $candidates
     * @return array
     */
    private function selectByPriority($candidates) {
        // If candidates have priority field, sort by it
        if (isset($candidates[0]['priority'])) {
            usort($candidates, function($a, $b) {
                return $a['priority'] - $b['priority'];
            });
        }

        return $candidates[0];
    }

    /**
     * Weighted random selection
     * @param array $candidates
     * @return array
     */
    private function selectWeighted($candidates) {
        if (!isset($candidates[0]['weight'])) {
            return $candidates[0];
        }

        $totalWeight = array_sum(array_column($candidates, 'weight'));
        $random = mt_rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($candidates as $candidate) {
            $currentWeight += $candidate['weight'];
            if ($random <= $currentWeight) {
                return $candidate;
            }
        }

        return $candidates[0];
    }

    /**
     * Generate reason string
     * @param array|null $rule
     * @param array|null $zone
     * @param string $strategy
     * @return string
     */
    private function generateReason($rule, $zone, $strategy) {
        $parts = [];

        if ($rule) {
            $parts[] = "Rule: {$rule['name']}";
        }

        if ($zone) {
            $parts[] = "Zone: {$zone['name']}";
        }

        $parts[] = "Strategy: {$strategy}";

        return implode(' | ', $parts);
    }

    /**
     * Reassign lead to different agent
     * @param int $leadId
     * @param int $newAgentId
     * @param int $userId Who is doing the reassignment
     * @return bool
     */
    public function reassignLead($leadId, $newAgentId, $userId) {
        $lead = $this->leadModel->findById($leadId);
        if (!$lead) {
            return false;
        }

        // Check if new agent has capacity
        if (!$this->userModel->hasCapacity($newAgentId)) {
            return false;
        }

        // Update lead assignment
        $this->leadModel->assignToAgent($leadId, $newAgentId, null);

        // Log manual reassignment
        $this->logModel->logDecision([
            'tenant_id' => $lead['tenant_id'],
            'lead_id' => $leadId,
            'selected_agent_id' => $newAgentId,
            'rule_id' => null,
            'zone_id' => null,
            'reason' => "Manual reassignment by user {$userId}",
            'strategy_used' => 'manual'
        ]);

        return true;
    }
}

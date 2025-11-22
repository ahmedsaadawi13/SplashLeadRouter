<?php
// FILE: /app/models/SubscriptionPlan.php

require_once __DIR__ . '/../core/Model.php';

/**
 * SubscriptionPlan Model
 * Manages subscription plans
 */
class SubscriptionPlan extends Model {
    protected $table = 'subscription_plans';

    /**
     * Get all active plans
     * @return array
     */
    public function getActivePlans() {
        return $this->findAll(['is_active' => 1]);
    }

    /**
     * Get plan by slug
     * @param string $slug
     * @return array|null
     */
    public function findBySlug($slug) {
        return $this->findOne(['slug' => $slug]);
    }

    /**
     * Get plan features
     * @param int $planId
     * @return array
     */
    public function getFeatures($planId) {
        $plan = $this->findById($planId);
        if (!$plan || empty($plan['features'])) {
            return [];
        }

        return json_decode($plan['features'], true) ?: [];
    }
}

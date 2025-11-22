<?php
// FILE: /app/helpers/Email.php

/**
 * Email Class
 * Handles email sending (simulated for now, can be extended with SMTP)
 */
class Email {
    private $to;
    private $subject;
    private $body;
    private $from;
    private $headers = [];

    /**
     * Set recipient
     * @param string $email
     * @return self
     */
    public function to($email) {
        $this->to = $email;
        return $this;
    }

    /**
     * Set subject
     * @param string $subject
     * @return self
     */
    public function subject($subject) {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set body
     * @param string $body
     * @return self
     */
    public function body($body) {
        $this->body = $body;
        return $this;
    }

    /**
     * Set from address
     * @param string $email
     * @param string $name
     * @return self
     */
    public function from($email, $name = '') {
        $this->from = $name ? "{$name} <{$email}>" : $email;
        return $this;
    }

    /**
     * Send email
     * @return bool
     */
    public function send() {
        // For now, we'll just log the email to a file
        // In production, this would use SMTP or a mail service

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->body,
            'from' => $this->from ?: 'noreply@splashleadrouter.com',
        ];

        $logFile = __DIR__ . '/../../storage/email_log.txt';
        $logLine = json_encode($logEntry) . PHP_EOL;

        file_put_contents($logFile, $logLine, FILE_APPEND);

        return true;
    }

    /**
     * Send lead assignment notification
     * @param array $agent
     * @param array $lead
     * @return bool
     */
    public static function sendLeadAssignment($agent, $lead) {
        $email = new self();

        $body = "Hello {$agent['name']},\n\n";
        $body .= "A new lead has been assigned to you:\n\n";
        $body .= "Name: {$lead['name']}\n";
        $body .= "Phone: {$lead['phone']}\n";
        $body .= "Email: {$lead['email']}\n";
        $body .= "Source: {$lead['source']}\n";
        $body .= "Location: {$lead['preferred_city']}, {$lead['preferred_area']}\n";
        $body .= "Budget: " . ($lead['budget_min'] ? '$' . number_format($lead['budget_min']) : 'N/A') . " - " . ($lead['budget_max'] ? '$' . number_format($lead['budget_max']) : 'N/A') . "\n\n";
        $body .= "Please contact this lead as soon as possible.\n\n";
        $body .= "Best regards,\nSplashLeadRouter";

        return $email->to($agent['email'])
            ->subject('New Lead Assignment')
            ->body($body)
            ->from('noreply@splashleadrouter.com', 'SplashLeadRouter')
            ->send();
    }

    /**
     * Send daily summary to tenant admin
     * @param array $admin
     * @param array $stats
     * @return bool
     */
    public static function sendDailySummary($admin, $stats) {
        $email = new self();

        $body = "Hello {$admin['name']},\n\n";
        $body .= "Here's your daily lead summary:\n\n";
        $body .= "Total leads today: {$stats['total_leads']}\n";
        $body .= "Assigned: {$stats['assigned']}\n";
        $body .= "Contacted: {$stats['contacted']}\n";
        $body .= "Qualified: {$stats['qualified']}\n";
        $body .= "Closed Won: {$stats['closed_won']}\n\n";
        $body .= "Keep up the great work!\n\n";
        $body .= "Best regards,\nSplashLeadRouter";

        return $email->to($admin['email'])
            ->subject('Daily Lead Summary')
            ->body($body)
            ->from('noreply@splashleadrouter.com', 'SplashLeadRouter')
            ->send();
    }
}

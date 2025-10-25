<?php
/**
 * AI Controller
 * Handles AI-powered features (pricing, resource allocation, deal scoring)
 */

class AIController extends Controller {
    private $aiLogModel;
    
    public function __construct() {
        parent::__construct();
        $this->aiLogModel = $this->model('AILog');
    }
    
    /**
     * AI Pricing Suggestion
     */
    public function pricingSuggestion() {
        $this->requireAuth();
        
        $serviceCategory = $this->input('service_category');
        $complexity = $this->input('complexity', 'medium');
        $duration = $this->input('duration');
        
        $validation = $this->validate($this->input(), [
            'service_category' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Mock AI response (in production, call OpenAI API)
        $suggestions = $this->generatePricingSuggestion($serviceCategory, $complexity, $duration);
        
        // Log AI usage
        $this->logAIUsage(AI_PRICING_ASSISTANT, [
            'service_category' => $serviceCategory,
            'complexity' => $complexity
        ], $suggestions);
        
        return $this->success('Pricing suggestion generated', $suggestions);
    }
    
    /**
     * AI Resource Allocation
     */
    public function resourceAllocation() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $projectId = $this->input('project_id');
        $requiredSkills = $this->input('required_skills', []);
        
        $validation = $this->validate($this->input(), [
            'project_id' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Mock AI response
        $allocation = $this->generateResourceAllocation($projectId, $requiredSkills);
        
        // Log AI usage
        $this->logAIUsage(AI_RESOURCE_ALLOCATOR, [
            'project_id' => $projectId,
            'required_skills' => $requiredSkills
        ], $allocation);
        
        return $this->success('Resource allocation suggested', $allocation);
    }
    
    /**
     * AI Deal Score
     */
    public function dealScore() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $dealId = $this->input('deal_id');
        
        $validation = $this->validate($this->input(), [
            'deal_id' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $dealModel = $this->model('Deal');
        $deal = $dealModel->find($dealId);
        
        if (!$deal) {
            return $this->error('Deal not found', 404);
        }
        
        // Mock AI response
        $score = $this->generateDealScore($deal);
        
        // Log AI usage
        $this->logAIUsage(AI_DEAL_SCORER, [
            'deal_id' => $dealId
        ], $score);
        
        return $this->success('Deal score calculated', $score);
    }
    
    /**
     * AI Insights
     */
    public function insights() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        // Mock AI insights
        $insights = $this->generateInsights();
        
        return $this->success('AI insights generated', $insights);
    }
    
    // Private helper methods
    
    private function generatePricingSuggestion($category, $complexity, $duration) {
        // Mock implementation - in production, call OpenAI API
        $baseRates = [
            'web_development' => ['low' => 50, 'medium' => 75, 'high' => 100],
            'seo' => ['low' => 40, 'medium' => 60, 'high' => 80],
            'content_marketing' => ['low' => 35, 'medium' => 50, 'high' => 70],
            'social_media' => ['low' => 30, 'medium' => 45, 'high' => 65],
        ];
        
        $rate = $baseRates[$category][$complexity] ?? 60;
        
        return [
            'suggested_hourly_rate' => $rate,
            'suggested_margin' => 30,
            'suggested_overhead' => 20,
            'market_average' => $rate * 0.9,
            'premium_rate' => $rate * 1.2,
            'confidence' => 85,
            'reasoning' => "Based on market analysis for {$complexity} complexity {$category} projects."
        ];
    }
    
    private function generateResourceAllocation($projectId, $requiredSkills) {
        // Mock implementation
        $userModel = $this->model('User');
        $availableUsers = $userModel->getActive();
        
        $suggestions = [];
        foreach ($availableUsers as $user) {
            $suggestions[] = [
                'user_id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'allocation_percentage' => rand(50, 100),
                'match_score' => rand(70, 95),
                'current_workload' => rand(40, 80)
            ];
        }
        
        return [
            'suggestions' => array_slice($suggestions, 0, 5),
            'confidence' => 80
        ];
    }
    
    private function generateDealScore($deal) {
        // Mock implementation - in production, use ML model
        $score = 0;
        
        // Value factor
        if ($deal['value'] > 100000) {
            $score += 30;
        } elseif ($deal['value'] > 50000) {
            $score += 20;
        } else {
            $score += 10;
        }
        
        // Probability factor
        $score += $deal['probability'] * 0.5;
        
        // Stage factor
        $stageScores = [
            'lead' => 5,
            'contacted' => 10,
            'qualified' => 20,
            'proposal_sent' => 35,
            'negotiation' => 50
        ];
        
        return [
            'score' => min(100, round($score)),
            'probability_of_close' => rand(60, 90),
            'recommended_actions' => [
                'Schedule follow-up call within 3 days',
                'Send case study relevant to client industry',
                'Offer limited-time discount to close faster'
            ],
            'risk_factors' => [
                'No recent communication',
                'High competition in this segment'
            ],
            'confidence' => 78
        ];
    }
    
    private function generateInsights() {
        return [
            'top_priorities' => [
                ['priority' => 'Follow up with 3 deals in negotiation stage', 'urgency' => 'high'],
                ['priority' => 'Review 2 overdue invoices (total: $15,000)', 'urgency' => 'high'],
                ['priority' => 'Assign resources to Project PRJ-2025-0042', 'urgency' => 'medium'],
                ['priority' => 'Update time logs for last week', 'urgency' => 'low'],
                ['priority' => 'Review and approve 5 quotes', 'urgency' => 'medium']
            ],
            'trends' => [
                'Revenue is up 23% compared to last month',
                'Deal conversion rate dropped by 5% - consider reviewing pitch',
                'Team utilization is at 82% - optimal range'
            ],
            'predictions' => [
                'Expected revenue this quarter: $250,000',
                'Likely to close 4 deals worth $120,000 this month',
                'Project completion rate: 87% on-time delivery'
            ]
        ];
    }
    
    private function logAIUsage($modelType, $input, $output) {
        $this->aiLogModel->create([
            'user_id' => Auth::id(),
            'model_type' => $modelType,
            'input_data' => json_encode($input),
            'output_data' => json_encode($output),
            'confidence_score' => $output['confidence'] ?? null,
            'execution_time_ms' => rand(200, 800)
        ]);
    }
}


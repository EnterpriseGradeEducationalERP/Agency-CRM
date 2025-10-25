<?php
/**
 * Dashboard Controller
 * Handles dashboard data for different user roles
 */

class DashboardController extends Controller {
    
    /**
     * Admin/C-Level Dashboard
     */
    public function admin() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $projectModel = $this->model('Project');
        $clientModel = $this->model('Client');
        $invoiceModel = $this->model('Invoice');
        $paymentModel = $this->model('Payment');
        $dealModel = $this->model('Deal');
        
        // Revenue metrics
        $revenue = $this->getRevenueMetrics($paymentModel);
        
        // Client metrics
        $clientStats = [
            'total' => $clientModel->count(['status' => 'active']),
            'new_this_month' => $this->getNewClientsThisMonth($clientModel)
        ];
        
        // Project metrics
        $projectStats = [
            'active' => $projectModel->count(['status' => PROJECT_ACTIVE]),
            'completed' => $projectModel->count(['status' => PROJECT_COMPLETED]),
            'on_hold' => $projectModel->count(['status' => PROJECT_ON_HOLD])
        ];
        
        // Invoice metrics
        $invoiceStats = [
            'pending' => $invoiceModel->count(['status' => INVOICE_SENT]),
            'overdue' => count($invoiceModel->getOverdue()),
            'paid_this_month' => $this->getPaidInvoicesThisMonth($invoiceModel)
        ];
        
        // Deal pipeline value
        $pipelineValue = $this->getPipelineValue($dealModel);
        
        // Team utilization
        $teamUtilization = $this->getTeamUtilization();
        
        // Recent activities
        $recentProjects = $projectModel->all([], 'created_at DESC', 5);
        $recentInvoices = $invoiceModel->all([], 'created_at DESC', 5);
        
        $dashboard = [
            'revenue' => $revenue,
            'clients' => $clientStats,
            'projects' => $projectStats,
            'invoices' => $invoiceStats,
            'pipeline_value' => $pipelineValue,
            'team_utilization' => $teamUtilization,
            'recent_projects' => $recentProjects,
            'recent_invoices' => $recentInvoices
        ];
        
        return $this->success('Admin dashboard data retrieved', $dashboard);
    }
    
    /**
     * Project Manager Dashboard
     */
    public function projectManager() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $projectModel = $this->model('Project');
        $taskModel = $this->model('Task');
        
        $userId = Auth::id();
        
        // My projects
        $myProjects = $projectModel->all(['project_manager_id' => $userId], 'created_at DESC');
        
        // Task stats across my projects
        $taskStats = $this->getTaskStatsByManager($userId);
        
        // Overdue tasks
        $overdueTasks = $this->getOverdueTasksByManager($userId);
        
        // Team workload
        $teamWorkload = $this->getTeamWorkloadByManager($userId);
        
        // Budget vs actual
        $budgetStats = $this->getBudgetStatsByManager($userId);
        
        $dashboard = [
            'my_projects' => $myProjects,
            'task_stats' => $taskStats,
            'overdue_tasks' => $overdueTasks,
            'team_workload' => $teamWorkload,
            'budget_stats' => $budgetStats
        ];
        
        return $this->success('Project Manager dashboard data retrieved', $dashboard);
    }
    
    /**
     * Sales Dashboard
     */
    public function sales() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $dealModel = $this->model('Deal');
        $quoteModel = $this->model('Quote');
        $clientModel = $this->model('Client');
        
        // Pipeline summary
        $pipelineStages = $this->model('PipelineStage')->getActive();
        foreach ($pipelineStages as &$stage) {
            $stage['deals'] = $dealModel->getByStage($stage['id']);
            $stage['total_value'] = $dealModel->getTotalValueByStage($stage['id']);
        }
        
        // Conversion metrics
        $conversionRate = $this->getConversionRate($dealModel);
        
        // Quote stats
        $quoteStats = [
            'sent_this_month' => $this->getQuotesSentThisMonth($quoteModel),
            'accepted_rate' => $this->getQuoteAcceptanceRate($quoteModel)
        ];
        
        // Top clients by value
        $topClients = $this->getTopClientsByValue($clientModel);
        
        // Upcoming follow-ups
        $upcomingFollowups = $this->getUpcomingFollowups($dealModel);
        
        $dashboard = [
            'pipeline' => $pipelineStages,
            'conversion_rate' => $conversionRate,
            'quotes' => $quoteStats,
            'top_clients' => $topClients,
            'upcoming_followups' => $upcomingFollowups
        ];
        
        return $this->success('Sales dashboard data retrieved', $dashboard);
    }
    
    /**
     * Team Member Dashboard
     */
    public function team() {
        $this->requireAuth();
        
        $taskModel = $this->model('Task');
        $timeLogModel = $this->model('TimeLog');
        $projectModel = $this->model('Project');
        
        $userId = Auth::id();
        
        // My tasks
        $myTasks = $taskModel->getAssignedTo($userId);
        
        // Today's time
        $todayTime = $this->getTodayTime($timeLogModel, $userId);
        
        // This week's time
        $weekTime = $this->getWeekTime($timeLogModel, $userId);
        
        // Active timer
        $activeTimer = $timeLogModel->getActiveTimer($userId);
        
        // My projects
        $myProjects = $this->getMyProjects($userId);
        
        $dashboard = [
            'my_tasks' => $myTasks,
            'today_time' => $todayTime,
            'week_time' => $weekTime,
            'active_timer' => $activeTimer,
            'my_projects' => $myProjects
        ];
        
        return $this->success('Team dashboard data retrieved', $dashboard);
    }
    
    // Helper methods
    
    private function getRevenueMetrics($paymentModel) {
        $thisMonth = $paymentModel->getSuccessful(date('Y-m-01'), date('Y-m-t'));
        $lastMonth = $paymentModel->getSuccessful(date('Y-m-01', strtotime('-1 month')), date('Y-m-t', strtotime('-1 month')));
        
        $thisMonthTotal = array_sum(array_column($thisMonth, 'amount'));
        $lastMonthTotal = array_sum(array_column($lastMonth, 'amount'));
        
        return [
            'this_month' => $thisMonthTotal,
            'last_month' => $lastMonthTotal,
            'growth' => $lastMonthTotal > 0 ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 2) : 0
        ];
    }
    
    private function getNewClientsThisMonth($clientModel) {
        $sql = "SELECT COUNT(*) as count FROM clients 
                WHERE created_at >= :start_date AND created_at <= :end_date";
        
        $result = $this->db->fetchOne($sql, [
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t')
        ]);
        
        return $result['count'] ?? 0;
    }
    
    private function getPaidInvoicesThisMonth($invoiceModel) {
        $sql = "SELECT COUNT(*) as count FROM invoices 
                WHERE status = :status 
                AND updated_at >= :start_date AND updated_at <= :end_date";
        
        $result = $this->db->fetchOne($sql, [
            'status' => INVOICE_PAID,
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t')
        ]);
        
        return $result['count'] ?? 0;
    }
    
    private function getPipelineValue($dealModel) {
        $sql = "SELECT SUM(value) as total FROM deals WHERE status = 'open'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }
    
    private function getTeamUtilization() {
        // TODO: Implement team utilization calculation
        return ['average' => 75, 'details' => []];
    }
    
    private function getTaskStatsByManager($managerId) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN t.due_date < CURDATE() AND t.status != 'done' THEN 1 ELSE 0 END) as overdue
                FROM tasks t 
                INNER JOIN projects p ON t.project_id = p.id 
                WHERE p.project_manager_id = :manager_id";
        
        return $this->db->fetchOne($sql, ['manager_id' => $managerId]);
    }
    
    private function getOverdueTasksByManager($managerId) {
        $sql = "SELECT t.* FROM tasks t 
                INNER JOIN projects p ON t.project_id = p.id 
                WHERE p.project_manager_id = :manager_id 
                AND t.due_date < CURDATE() 
                AND t.status != 'done' 
                ORDER BY t.due_date ASC LIMIT 10";
        
        return $this->db->fetchAll($sql, ['manager_id' => $managerId]);
    }
    
    private function getTeamWorkloadByManager($managerId) {
        // TODO: Implement team workload calculation
        return [];
    }
    
    private function getBudgetStatsByManager($managerId) {
        // TODO: Implement budget vs actual calculation
        return ['total_budget' => 0, 'spent' => 0, 'remaining' => 0];
    }
    
    private function getConversionRate($dealModel) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won
                FROM deals";
        
        $result = $this->db->fetchOne($sql);
        $total = $result['total'] ?? 0;
        $won = $result['won'] ?? 0;
        
        return $total > 0 ? round(($won / $total) * 100, 2) : 0;
    }
    
    private function getQuotesSentThisMonth($quoteModel) {
        return $quoteModel->count([
            'status' => QUOTE_SENT,
            // TODO: Add date filter
        ]);
    }
    
    private function getQuoteAcceptanceRate($quoteModel) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted
                FROM quotes 
                WHERE status IN ('accepted', 'rejected')";
        
        $result = $this->db->fetchOne($sql);
        $total = $result['total'] ?? 0;
        $accepted = $result['accepted'] ?? 0;
        
        return $total > 0 ? round(($accepted / $total) * 100, 2) : 0;
    }
    
    private function getTopClientsByValue($clientModel) {
        $sql = "SELECT c.*, SUM(p.amount) as total_paid 
                FROM clients c 
                LEFT JOIN invoices i ON c.id = i.client_id 
                LEFT JOIN payments p ON i.id = p.invoice_id 
                WHERE p.status = 'success' 
                GROUP BY c.id 
                ORDER BY total_paid DESC 
                LIMIT 10";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getUpcomingFollowups($dealModel) {
        $sql = "SELECT * FROM deals 
                WHERE next_followup >= CURDATE() 
                AND status = 'open' 
                ORDER BY next_followup ASC 
                LIMIT 10";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getTodayTime($timeLogModel, $userId) {
        $sql = "SELECT SUM(duration_minutes) as total 
                FROM time_logs 
                WHERE user_id = :user_id 
                AND DATE(start_time) = CURDATE()";
        
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return round(($result['total'] ?? 0) / 60, 2);
    }
    
    private function getWeekTime($timeLogModel, $userId) {
        $sql = "SELECT SUM(duration_minutes) as total 
                FROM time_logs 
                WHERE user_id = :user_id 
                AND YEARWEEK(start_time) = YEARWEEK(CURDATE())";
        
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return round(($result['total'] ?? 0) / 60, 2);
    }
    
    private function getMyProjects($userId) {
        $sql = "SELECT DISTINCT p.* FROM projects p 
                INNER JOIN project_team pt ON p.id = pt.project_id 
                WHERE pt.user_id = :user_id 
                AND p.status IN ('planned', 'active') 
                ORDER BY p.start_date DESC";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
}


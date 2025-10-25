<?php
/**
 * Report Controller
 * Handles report generation and export
 */

class ReportController extends Controller {
    
    /**
     * Financial Report
     */
    public function financial() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_FINANCE_OFFICER]);
        
        $startDate = $this->input('start_date', date('Y-m-01'));
        $endDate = $this->input('end_date', date('Y-m-t'));
        
        $paymentModel = $this->model('Payment');
        $invoiceModel = $this->model('Invoice');
        
        // Revenue
        $payments = $paymentModel->getSuccessful($startDate, $endDate);
        $totalRevenue = array_sum(array_column($payments, 'amount'));
        
        // Outstanding invoices
        $overdue = $invoiceModel->getOverdue();
        $outstanding = array_sum(array_column($overdue, 'balance'));
        
        // Revenue by client
        $revenueByClient = $this->getRevenueByClient($startDate, $endDate);
        
        // Revenue by project
        $revenueByProject = $this->getRevenueByProject($startDate, $endDate);
        
        $report = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'outstanding_amount' => $outstanding,
                'payments_count' => count($payments),
                'overdue_invoices' => count($overdue)
            ],
            'revenue_by_client' => $revenueByClient,
            'revenue_by_project' => $revenueByProject,
            'payments' => $payments
        ];
        
        return $this->success('Financial report generated', $report);
    }
    
    /**
     * Productivity Report
     */
    public function productivity() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $startDate = $this->input('start_date', date('Y-m-01'));
        $endDate = $this->input('end_date', date('Y-m-t'));
        $projectId = $this->input('project_id');
        
        // Time logged
        $timeStats = $this->getTimeStats($startDate, $endDate, $projectId);
        
        // Tasks completed
        $taskStats = $this->getTaskStats($startDate, $endDate, $projectId);
        
        // Productivity by user
        $userProductivity = $this->getUserProductivity($startDate, $endDate, $projectId);
        
        $report = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'time_stats' => $timeStats,
            'task_stats' => $taskStats,
            'user_productivity' => $userProductivity
        ];
        
        return $this->success('Productivity report generated', $report);
    }
    
    /**
     * Utilization Report
     */
    public function utilization() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $startDate = $this->input('start_date', date('Y-m-01'));
        $endDate = $this->input('end_date', date('Y-m-t'));
        
        // Team utilization
        $teamUtilization = $this->getTeamUtilization($startDate, $endDate);
        
        // Project utilization
        $projectUtilization = $this->getProjectUtilization($startDate, $endDate);
        
        $report = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'team_utilization' => $teamUtilization,
            'project_utilization' => $projectUtilization
        ];
        
        return $this->success('Utilization report generated', $report);
    }
    
    /**
     * Export Report
     */
    public function export() {
        $this->requireAuth();
        
        $reportType = $this->input('report_type');
        $format = $this->input('format', 'csv');
        
        // TODO: Implement actual export functionality
        
        return $this->success('Export functionality coming soon', [
            'report_type' => $reportType,
            'format' => $format
        ]);
    }
    
    // Helper methods
    
    private function getRevenueByClient($startDate, $endDate) {
        $sql = "SELECT c.company_name, SUM(p.amount) as total_revenue 
                FROM clients c 
                INNER JOIN invoices i ON c.id = i.client_id 
                INNER JOIN payments p ON i.id = p.invoice_id 
                WHERE p.status = 'success' 
                AND p.payment_date >= :start_date 
                AND p.payment_date <= :end_date 
                GROUP BY c.id 
                ORDER BY total_revenue DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    private function getRevenueByProject($startDate, $endDate) {
        $sql = "SELECT p.name, SUM(pay.amount) as total_revenue 
                FROM projects p 
                INNER JOIN invoices i ON p.id = i.project_id 
                INNER JOIN payments pay ON i.id = pay.invoice_id 
                WHERE pay.status = 'success' 
                AND pay.payment_date >= :start_date 
                AND pay.payment_date <= :end_date 
                GROUP BY p.id 
                ORDER BY total_revenue DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    private function getTimeStats($startDate, $endDate, $projectId) {
        $sql = "SELECT 
                SUM(duration_minutes) as total_minutes,
                SUM(CASE WHEN is_billable = 1 THEN duration_minutes ELSE 0 END) as billable_minutes,
                SUM(amount) as total_amount
                FROM time_logs 
                WHERE start_time >= :start_date 
                AND start_time <= :end_date";
        
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        
        return [
            'total_hours' => round(($result['total_minutes'] ?? 0) / 60, 2),
            'billable_hours' => round(($result['billable_minutes'] ?? 0) / 60, 2),
            'total_amount' => $result['total_amount'] ?? 0
        ];
    }
    
    private function getTaskStats($startDate, $endDate, $projectId) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed
                FROM tasks 
                WHERE created_at >= :start_date 
                AND created_at <= :end_date";
        
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        return $this->db->fetchOne($sql, $params);
    }
    
    private function getUserProductivity($startDate, $endDate, $projectId) {
        $sql = "SELECT 
                u.first_name, u.last_name,
                SUM(tl.duration_minutes) as total_minutes,
                COUNT(DISTINCT t.id) as tasks_completed
                FROM users u 
                LEFT JOIN time_logs tl ON u.id = tl.user_id 
                  AND tl.start_time >= :start_date 
                  AND tl.start_time <= :end_date
                LEFT JOIN tasks t ON u.id = t.assigned_to 
                  AND t.status = 'done' 
                  AND t.completed_at >= :start_date 
                  AND t.completed_at <= :end_date
                WHERE u.status = 'active'";
        
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($projectId) {
            $sql .= " AND (tl.project_id = :project_id OR t.project_id = :project_id)";
            $params['project_id'] = $projectId;
        }
        
        $sql .= " GROUP BY u.id ORDER BY total_minutes DESC";
        
        $users = $this->db->fetchAll($sql, $params);
        
        foreach ($users as &$user) {
            $user['total_hours'] = round(($user['total_minutes'] ?? 0) / 60, 2);
            unset($user['total_minutes']);
        }
        
        return $users;
    }
    
    private function getTeamUtilization($startDate, $endDate) {
        // TODO: Implement actual utilization calculation
        return [];
    }
    
    private function getProjectUtilization($startDate, $endDate) {
        // TODO: Implement project utilization calculation
        return [];
    }
}


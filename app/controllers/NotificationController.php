<?php
/**
 * Notification Controller
 * Handles user notifications
 */

class NotificationController extends Controller {
    private $notificationModel;
    
    public function __construct() {
        parent::__construct();
        $this->notificationModel = $this->model('Notification');
    }
    
    /**
     * Get user notifications
     */
    public function index() {
        $this->requireAuth();
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 50);
        $isRead = $this->input('is_read');
        
        $conditions = ['user_id' => Auth::id()];
        if ($isRead !== null) {
            $conditions['is_read'] = $isRead;
        }
        
        $result = $this->notificationModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        // Also return unread count
        $unreadCount = $this->notificationModel->count([
            'user_id' => Auth::id(),
            'is_read' => 0
        ]);
        
        $result['unread_count'] = $unreadCount;
        
        return $this->success('Notifications retrieved', $result);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id) {
        $this->requireAuth();
        
        $notification = $this->notificationModel->find($id);
        
        if (!$notification) {
            return $this->error('Notification not found', 404);
        }
        
        // Check ownership
        if ($notification['user_id'] != Auth::id()) {
            return $this->error('Forbidden', 403);
        }
        
        $this->notificationModel->update($id, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->success('Notification marked as read');
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead() {
        $this->requireAuth();
        
        $this->notificationModel->markAllAsRead(Auth::id());
        
        return $this->success('All notifications marked as read');
    }
    
    /**
     * Delete notification
     */
    public function delete($id) {
        $this->requireAuth();
        
        $notification = $this->notificationModel->find($id);
        
        if (!$notification) {
            return $this->error('Notification not found', 404);
        }
        
        // Check ownership
        if ($notification['user_id'] != Auth::id()) {
            return $this->error('Forbidden', 403);
        }
        
        $this->notificationModel->delete($id);
        return $this->success('Notification deleted');
    }
}


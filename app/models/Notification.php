<?php
/**
 * Notification Model
 * Handles notification data operations
 */

class Notification extends Model {
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'read_at'
    ];
    protected $timestamps = true;
    
    /**
     * Create notification for user
     */
    public function notify($userId, $type, $title, $message, $link = null) {
        return $this->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0
        ]);
    }
    
    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId) {
        return $this->db->update(
            'notifications',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'user_id = :user_id AND is_read = 0',
            ['user_id' => $userId]
        );
    }
    
    /**
     * Get unread notifications
     */
    public function getUnread($userId) {
        return $this->all(['user_id' => $userId, 'is_read' => 0], 'created_at DESC');
    }
    
    /**
     * Delete old notifications
     */
    public function deleteOld($days = 30) {
        $sql = "DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        return $this->db->query($sql, ['days' => $days]);
    }
}


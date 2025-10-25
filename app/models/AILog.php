<?php
/**
 * AI Log Model
 * Handles AI usage logging
 */

class AILog extends Model {
    protected $table = 'ai_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'model_type',
        'input_data',
        'output_data',
        'confidence_score',
        'execution_time_ms'
    ];
    protected $timestamps = true;
    
    /**
     * Get logs by model type
     */
    public function getByModelType($modelType) {
        return $this->all(['model_type' => $modelType], 'created_at DESC');
    }
    
    /**
     * Get logs by user
     */
    public function getByUser($userId) {
        return $this->all(['user_id' => $userId], 'created_at DESC');
    }
    
    /**
     * Get usage stats
     */
    public function getUsageStats() {
        $sql = "SELECT 
                model_type,
                COUNT(*) as usage_count,
                AVG(confidence_score) as avg_confidence,
                AVG(execution_time_ms) as avg_execution_time
                FROM ai_logs 
                GROUP BY model_type";
        
        return $this->db->fetchAll($sql);
    }
}


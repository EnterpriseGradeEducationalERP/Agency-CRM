<?php
/**
 * Pipeline Stage Model
 * Handles pipeline stage data operations
 */

class PipelineStage extends Model {
    protected $table = 'pipeline_stages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'color',
        'order_position',
        'is_active'
    ];
    protected $timestamps = true;
    
    /**
     * Get active stages
     */
    public function getActive() {
        return $this->all(['is_active' => 1], 'order_position ASC');
    }
}


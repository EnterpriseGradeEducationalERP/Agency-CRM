<?php
/**
 * File Model
 * Handles file data operations
 */

class File extends Model {
    protected $table = 'files';
    protected $primaryKey = 'id';
    protected $fillable = [
        'entity_type',
        'entity_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'file_type',
        'mime_type',
        'uploaded_by'
    ];
    protected $timestamps = true;
    
    /**
     * Get files by entity
     */
    public function getByEntity($entityType, $entityId) {
        return $this->all([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ], 'created_at DESC');
    }
    
    /**
     * Get files by uploader
     */
    public function getByUploader($userId) {
        return $this->all(['uploaded_by' => $userId], 'created_at DESC');
    }
}


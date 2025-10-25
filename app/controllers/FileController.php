<?php
/**
 * File Controller
 * Handles file uploads and downloads
 */

class FileController extends Controller {
    private $fileModel;
    
    public function __construct() {
        parent::__construct();
        $this->fileModel = $this->model('File');
    }
    
    /**
     * Upload file
     */
    public function upload() {
        $this->requireAuth();
        
        $entityType = $this->input('entity_type');
        $entityId = $this->input('entity_id');
        
        if (!$entityType || !$entityId) {
            return $this->error('Entity type and ID are required', 400);
        }
        
        if (!isset($_FILES['file'])) {
            return $this->error('No file uploaded', 400);
        }
        
        $file = $_FILES['file'];
        
        // Validate file
        $maxSize = $this->config['upload_max_size'];
        if ($file['size'] > $maxSize) {
            return $this->error('File size exceeds maximum allowed size', 400);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->config['allowed_file_types'])) {
            return $this->error('File type not allowed', 400);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = $this->config['upload_path'] . '/' . $entityType;
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filePath = $uploadPath . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return $this->error('Failed to upload file', 500);
        }
        
        // Save to database
        $fileId = $this->fileModel->create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'filename' => $filename,
            'original_filename' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $this->getFileType($extension),
            'mime_type' => $file['type'],
            'uploaded_by' => Auth::id()
        ]);
        
        $uploadedFile = $this->fileModel->find($fileId);
        
        return $this->success('File uploaded successfully', $uploadedFile, 201);
    }
    
    /**
     * Download file
     */
    public function download($id) {
        $this->requireAuth();
        
        $file = $this->fileModel->find($id);
        
        if (!$file) {
            return $this->error('File not found', 404);
        }
        
        if (!file_exists($file['file_path'])) {
            return $this->error('File not found on server', 404);
        }
        
        // Serve file
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
        header('Content-Length: ' . $file['file_size']);
        readfile($file['file_path']);
        exit;
    }
    
    /**
     * Delete file
     */
    public function delete($id) {
        $this->requireAuth();
        
        $file = $this->fileModel->find($id);
        
        if (!$file) {
            return $this->error('File not found', 404);
        }
        
        // Check permission
        if (!$this->hasRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]) && $file['uploaded_by'] != Auth::id()) {
            return $this->error('Forbidden', 403);
        }
        
        // Delete physical file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        // Delete from database
        $this->fileModel->delete($id);
        
        return $this->success('File deleted');
    }
    
    /**
     * Get file type category
     */
    private function getFileType($extension) {
        $types = [
            'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'],
            'spreadsheet' => ['xls', 'xlsx', 'csv'],
            'other' => []
        ];
        
        foreach ($types as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        
        return FILE_OTHER;
    }
}


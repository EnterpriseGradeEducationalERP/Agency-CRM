<?php
/**
 * Settings Controller
 * Handles application settings management
 */

class SettingsController extends Controller {
    private $settingsModel;
    
    public function __construct() {
        parent::__construct();
        $this->settingsModel = $this->model('Setting');
    }
    
    /**
     * Get all settings (grouped)
     */
    public function index() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $group = $this->input('group');
        
        if ($group) {
            $settings = $this->settingsModel->getByGroup($group);
        } else {
            $settings = $this->settingsModel->all([], 'group ASC, key ASC');
        }
        
        // Group settings
        $grouped = [];
        foreach ($settings as $setting) {
            $grouped[$setting['group']][] = $setting;
        }
        
        return $this->success('Settings retrieved', $grouped);
    }
    
    /**
     * Get single setting
     */
    public function show($key) {
        $this->requireAuth();
        
        $setting = $this->settingsModel->findWhere(['key' => $key]);
        
        if (!$setting) {
            return $this->error('Setting not found', 404);
        }
        
        return $this->success('Setting retrieved', $setting);
    }
    
    /**
     * Update setting
     */
    public function update($key) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $setting = $this->settingsModel->findWhere(['key' => $key]);
        
        if (!$setting) {
            return $this->error('Setting not found', 404);
        }
        
        $value = $this->input('value');
        
        if ($value === null) {
            return $this->error('Value is required', 400);
        }
        
        $this->settingsModel->setValue($key, $value);
        $updatedSetting = $this->settingsModel->findWhere(['key' => $key]);
        
        return $this->success('Setting updated', $updatedSetting);
    }
}


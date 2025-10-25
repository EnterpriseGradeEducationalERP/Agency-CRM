<?php
/**
 * Setting Model
 * Handles application settings
 */

class Setting extends Model {
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description'
    ];
    protected $timestamps = true;
    
    /**
     * Get setting value by key
     */
    public function getValue($key, $default = null) {
        $setting = $this->findWhere(['key' => $key]);
        
        if (!$setting) {
            return $default;
        }
        
        // Cast value based on type
        switch ($setting['type']) {
            case 'integer':
                return (int) $setting['value'];
            case 'decimal':
            case 'float':
                return (float) $setting['value'];
            case 'boolean':
                return (bool) $setting['value'];
            case 'json':
                return json_decode($setting['value'], true);
            default:
                return $setting['value'];
        }
    }
    
    /**
     * Set setting value
     */
    public function setValue($key, $value) {
        $setting = $this->findWhere(['key' => $key]);
        
        if ($setting) {
            return $this->update($setting['id'], ['value' => $value]);
        }
        
        return $this->create([
            'key' => $key,
            'value' => $value,
            'type' => 'string',
            'group' => 'general'
        ]);
    }
    
    /**
     * Get settings by group
     */
    public function getByGroup($group) {
        return $this->all(['group' => $group], 'key ASC');
    }
    
    /**
     * Get all settings as key-value array
     */
    public function getAllAsArray() {
        $settings = $this->all([], 'key ASC');
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->getValue($setting['key']);
        }
        
        return $result;
    }
}


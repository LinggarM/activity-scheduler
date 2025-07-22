<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table = 'activities';
    protected $primaryKey = 'id';
    
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'activity_name',
        'location_code',
        'location_name',
        'preferred_date',
        'selected_datetime',
        'weather_condition',
        'weather_condition_en',
        'temperature',
        'humidity',
        'wind_speed',
        'cloud_coverage',
        'status',
        'notes',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Validation rules
    protected $validationRules = [
        'activity_name' => 'required|min_length[3]|max_length[255]',
        'location_code' => 'required|min_length[10]|max_length[10]',
        'preferred_date' => 'required|valid_date',
        'selected_datetime' => 'required',
        'weather_condition' => 'required|max_length[100]',
        'temperature' => 'permit_empty|numeric',
        'humidity' => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'wind_speed' => 'permit_empty|numeric|greater_than_equal_to[0]',
        'cloud_coverage' => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'status' => 'permit_empty|in_list[scheduled,completed,cancelled]'
    ];
    
    protected $validationMessages = [
        'activity_name' => [
            'required' => 'Activity name is required',
            'min_length' => 'Activity name must be at least 3 characters long',
            'max_length' => 'Activity name cannot exceed 255 characters'
        ],
        'location_code' => [
            'required' => 'Location code is required',
            'min_length' => 'Invalid location code format',
            'max_length' => 'Invalid location code format'
        ],
        'preferred_date' => [
            'required' => 'Preferred date is required',
            'valid_date' => 'Please provide a valid date'
        ],
        'selected_datetime' => [
            'required' => 'Selected datetime is required'
        ],
        'weather_condition' => [
            'required' => 'Weather condition is required',
            'max_length' => 'Weather condition cannot exceed 100 characters'
        ],
        'humidity' => [
            'numeric' => 'Humidity must be a number',
            'greater_than_equal_to' => 'Humidity cannot be negative',
            'less_than_equal_to' => 'Humidity cannot exceed 100%'
        ],
        'wind_speed' => [
            'numeric' => 'Wind speed must be a number',
            'greater_than_equal_to' => 'Wind speed cannot be negative'
        ],
        'cloud_coverage' => [
            'numeric' => 'Cloud coverage must be a number',
            'greater_than_equal_to' => 'Cloud coverage cannot be negative',
            'less_than_equal_to' => 'Cloud coverage cannot exceed 100%'
        ]
    ];
    
    protected $skipValidation = false;
    
    // Custom methods
    
    /**
     * Get activities by date range
     */
    public function getActivitiesByDateRange($startDate, $endDate)
    {
        return $this->where('preferred_date >=', $startDate)
                   ->where('preferred_date <=', $endDate)
                   ->orderBy('preferred_date', 'ASC')
                   ->orderBy('selected_datetime', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get activities by status
     */
    public function getActivitiesByStatus($status)
    {
        return $this->where('status', $status)
                   ->orderBy('selected_datetime', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get upcoming activities
     */
    public function getUpcomingActivities($limit = 10)
    {
        return $this->where('selected_datetime >=', date('Y-m-d H:i:s'))
                   ->where('status !=', 'cancelled')
                   ->orderBy('selected_datetime', 'ASC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get activities by location
     */
    public function getActivitiesByLocation($locationCode)
    {
        return $this->where('location_code', $locationCode)
                   ->orderBy('selected_datetime', 'DESC')
                   ->findAll();
    }
    
    /**
     * Update activity status
     */
    public function updateActivityStatus($id, $status)
    {
        $validStatuses = ['scheduled', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get activity statistics
     */
    public function getActivityStatistics()
    {
        $db = \Config\Database::connect();
        
        $stats = [
            'total_activities' => $this->countAll(),
            'scheduled' => $this->where('status', 'scheduled')->countAllResults(false),
            'completed' => $this->where('status', 'completed')->countAllResults(false),
            'cancelled' => $this->where('status', 'cancelled')->countAllResults(false),
            'upcoming' => $this->where('selected_datetime >=', date('Y-m-d H:i:s'))
                              ->where('status', 'scheduled')
                              ->countAllResults(false)
        ];
        
        // Get activities by weather condition
        $weatherStats = $db->table($this->table)
                          ->select('weather_condition, COUNT(*) as count')
                          ->groupBy('weather_condition')
                          ->orderBy('count', 'DESC')
                          ->get()
                          ->getResultArray();
        
        $stats['by_weather'] = $weatherStats;
        
        return $stats;
    }
    
    /**
     * Search activities
     */
    public function searchActivities($keyword, $limit = 20)
    {
        return $this->groupStart()
                   ->like('activity_name', $keyword)
                   ->orLike('location_name', $keyword)
                   ->orLike('weather_condition', $keyword)
                   ->orLike('notes', $keyword)
                   ->groupEnd()
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get weather condition summary
     */
    public function getWeatherSummary($dateFrom = null, $dateTo = null)
    {
        $builder = $this->select('weather_condition, COUNT(*) as count, AVG(temperature) as avg_temp, AVG(humidity) as avg_humidity')
                       ->groupBy('weather_condition');
        
        if ($dateFrom) {
            $builder->where('preferred_date >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('preferred_date <=', $dateTo);
        }
        
        return $builder->orderBy('count', 'DESC')->findAll();
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data)
    {
        // Set default status if not provided
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'scheduled';
        }
        
        // Auto-generate location name from code if not provided
        if (!isset($data['data']['location_name']) && isset($data['data']['location_code'])) {
            $data['data']['location_name'] = $this->getLocationNameByCode($data['data']['location_code']);
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data)
    {
        // Update location name if location code is changed
        if (isset($data['data']['location_code']) && !isset($data['data']['location_name'])) {
            $data['data']['location_name'] = $this->getLocationNameByCode($data['data']['location_code']);
        }
        
        return $data;
    }
    
    /**
     * Helper method to get location name by code
     */
    private function getLocationNameByCode($locationCode)
    {
        $locations = [
            '3171010001' => 'Jakarta Pusat - Gambir',
            '3275010001' => 'Bogor - Bogor Tengah',
            '3276010001' => 'Depok - Pancoran Mas',
            '3171020001' => 'Jakarta Pusat - Tanah Abang',
            '3374010001' => 'Sleman - Depok',
            '3578010001' => 'Surabaya - Tegalsari',
            '3471010001' => 'Yogyakarta - Mantrijeron',
            '3273010001' => 'Bandung - Bandung Wetan',
            '3372010001' => 'Bantul - Bantul',
            '3671010001' => 'Palembang - Ilir Barat I'
        ];
        
        return isset($locations[$locationCode]) ? $locations[$locationCode] : 'Unknown Location';
    }
}
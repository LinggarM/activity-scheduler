<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use CodeIgniter\RESTful\ResourceController;

class Activity extends ResourceController
{
    protected $modelName = 'App\Models\ActivityModel';
    protected $format = 'json';

    public function getWeatherForecast()
    {
        $request = service('request');
        $location = $request->getGet('location');
        $date = $request->getGet('date') ?? date('Y-m-d');
        
        if (!$location) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Location parameter is required'
            ], 400);
        }

        try {
            // Call BMKG API
            $bmkgData = $this->fetchBMKGWeather($location);
            
            if (!$bmkgData) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to fetch weather data from BMKG'
                ], 500);
            }

            // Filter suitable weather conditions
            $suitableSlots = $this->filterSuitableWeather($bmkgData[0]['cuaca'], $date);

            return $this->respond([
                'status' => 'success',
                'data' => $suitableSlots,
                'source' => 'BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Weather API Error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    private function fetchBMKGWeather($locationCode)
    {
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->request('GET', "https://api.bmkg.go.id/publik/prakiraan-cuaca", [
                'query' => ['adm4' => $locationCode],
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'Activity-Scheduler/1.0',
                    'Accept' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                $body = $response->getBody();
                $data = json_decode($body, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($data['data'])) {
                    return $data['data'];
                }
            }

            // Fallback to mock data if API fails
            return $this->getMockWeatherData();

        } catch (\Exception $e) {
            log_message('error', 'BMKG API Error: ' . $e->getMessage());
            // Return mock data as fallback
            return $this->getMockWeatherData();
        }
    }

    private function filterSuitableWeather(array $weatherData, string $date)
    {
        $suitableConditions = ['cerah', 'clear', 'berawan', 'cloudy', 'partly cloudy', 'berawan sebagian'];
        $rainKeywords = ['hujan', 'rain', 'storm', 'thunderstorm'];
    
        $suitableSlots = [];
    
        foreach ($weatherData as $dailyForecasts) {
            foreach ($dailyForecasts as $forecast) {
                // Pastikan format date cocok
                $forecastDate = date('Y-m-d', strtotime($forecast['local_datetime']));
                if ($forecastDate !== $date) {
                    continue;
                }
    
                $weatherDesc = strtolower($forecast['weather_desc'] ?? '');
                $weatherDescEn = strtolower($forecast['weather_desc_en'] ?? '');
    
                // Cek apakah deskripsi cuaca cocok dengan kondisi yang sesuai
                $isSuitable = false;
                foreach ($suitableConditions as $condition) {
                    if (strpos($weatherDesc, $condition) !== false || strpos($weatherDescEn, $condition) !== false) {
                        $isSuitable = true;
                        break;
                    }
                }
    
                // Jika mengandung kata terkait hujan, langsung tidak cocok
                foreach ($rainKeywords as $keyword) {
                    if (strpos($weatherDesc, $keyword) !== false || strpos($weatherDescEn, $keyword) !== false) {
                        $isSuitable = false;
                        break;
                    }
                }
    
                if ($isSuitable) {
                    $suitableSlots[] = $forecast;
                }
            }
        }
    
        return $suitableSlots;
    }    

    public function scheduleActivity()
    {
        $request = service('request');
        $json = $request->getJSON();

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'activityName' => 'required|min_length[3]|max_length[255]',
            'location' => 'required',
            'preferredDate' => 'required|valid_date',
            'selectedTime' => 'required',
            'weatherCondition' => 'required',
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric'
        ]);

        if (!$validation->run((array)$json)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ], 400);
        }

        try {
            $model = new ActivityModel();
            $activityId = $model->insert([
                'activity_name' => $json->activityName,
                'location_code' => $json->location,
                'location_name' => $json->locationTitle,
                'preferred_date' => $json->preferredDate,
                'selected_datetime' => $json->selectedTime,
                'weather_condition' => $json->weatherCondition,
                'weather_condition_en' => $json->weatherConditionEn,
                'temperature' => $json->temperature,
                'humidity' => $json->humidity,
                'wind_speed' => $json->windSpeed,
                'wind_direction' => $json->windDirection,
                'cloud_coverage' => $json->cloudCoverage,
                'visibility' => $json->visibility,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            if ($activityId === false) {
                $errors = $model->errors();
                dd($errors);
            }

            if ($activityId) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Activity scheduled successfully',
                    'activity_id' => $activityId
                ], 201);
            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to schedule activity'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Schedule Activity Error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function getActivities()
    {
        try {
            $model = new ActivityModel();
            $activities = $model->orderBy('created_at', 'DESC')->findAll();

            return $this->respond([
                'status' => 'success',
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get Activities Error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    private function getMockWeatherData()
    {
        $mockData = [];
        $now = new \DateTime();
        
        // Generate 3 days of forecast data (8 forecasts per day)
        for ($i = 0; $i < 24; $i++) {
            $forecastTime = clone $now;
            $forecastTime->add(new \DateInterval('PT' . ($i * 3) . 'H'));
            
            $weatherConditions = [
                ['desc' => 'Cerah', 'desc_en' => 'Clear', 'suitable' => true],
                ['desc' => 'Berawan', 'desc_en' => 'Cloudy', 'suitable' => true],
                ['desc' => 'Berawan Sebagian', 'desc_en' => 'Partly Cloudy', 'suitable' => true],
                ['desc' => 'Hujan Ringan', 'desc_en' => 'Light Rain', 'suitable' => false],
                ['desc' => 'Hujan Sedang', 'desc_en' => 'Moderate Rain', 'suitable' => false],
                ['desc' => 'Hujan Lebat', 'desc_en' => 'Heavy Rain', 'suitable' => false]
            ];
            
            $weather = $weatherConditions[array_rand($weatherConditions)];
            
            $mockData[] = [
                'utc_datetime' => $forecastTime->format('Y-m-d H:i:s'),
                'local_datetime' => $forecastTime->format('Y-m-d H:i:s'),
                't' => rand(25, 35), // Temperature 25-35°C
                'hu' => rand(60, 90), // Humidity 60-90%
                'weather_desc' => $weather['desc'],
                'weather_desc_en' => $weather['desc_en'],
                'ws' => rand(5, 20), // Wind speed 5-20 km/h
                'wd' => ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][array_rand(['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'])],
                'tcc' => rand(0, 100), // Cloud cover 0-100%
                'vs_text' => '> 10',
                'analysis_date' => (new \DateTime())->format('Y-m-d\TH:i:s')
            ];
        }
        
        return $mockData;
    }

    public function getLocationOptions()
    {
        // Common location codes for Indonesian cities/districts
        $locations = [
            ['code' => '3171010001', 'name' => 'Jakarta Pusat - Gambir'],
            ['code' => '3275010001', 'name' => 'Bogor - Bogor Tengah'],
            ['code' => '3276010001', 'name' => 'Depok - Pancoran Mas'],
            ['code' => '3171020001', 'name' => 'Jakarta Pusat - Tanah Abang'],
            ['code' => '3374010001', 'name' => 'Sleman - Depok'],
            ['code' => '3578010001', 'name' => 'Surabaya - Tegalsari'],
            ['code' => '3471010001', 'name' => 'Yogyakarta - Mantrijeron'],
            ['code' => '3273010001', 'name' => 'Bandung - Bandung Wetan'],
            ['code' => '3372010001', 'name' => 'Bantul - Bantul'],
            ['code' => '3671010001', 'name' => 'Palembang - Ilir Barat I']
        ];

        return $this->respond([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    public function searchLocation()
    {
        $term = $this->request->getGet('q');
        $jsonPath = WRITEPATH . 'uploads\files\wilayah.json';

        if (!file_exists($jsonPath)) {
            return $this->response->setJSON([]);
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $results = [];

        foreach ($data as $item) {
            if (!$term || stripos($item['text'], $term) !== false) {
                $results[] = $item;
            }
        }

        return $this->response->setJSON($results);
    }
}
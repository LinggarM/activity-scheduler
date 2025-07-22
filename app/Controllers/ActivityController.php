<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use CodeIgniter\Controller;

class ActivityController extends Controller
{
    public function index()
    {
        return view('activity_form');
    }

    public function schedule()
    {
        $activityModel = new ActivityModel();

        $activityName = $this->request->getPost('activity_name');
        $location = $this->request->getPost('location');
        $preferredDate = $this->request->getPost('preferred_date');

        // Call the BMKG API to get the weather forecast
        $weatherData = $this->getWeatherForecast($location);

        // Filter suitable time slots
        $suitableSlots = $this->filterSuitableSlots($weatherData);

        // Save the activity to the database
        foreach ($suitableSlots as $slot) {
            $activityModel->save([
                'activity_name' => $activityName,
                'location' => $location,
                'preferred_date' => $preferredDate,
                'time_slot' => $slot
            ]);
        }

        return view('activity_form', ['suggested_slots' => $suitableSlots]);
    }

    private function getWeatherForecast($location)
    {
        // Replace with the actual BMKG API URL and parameters
        $url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$location}";

        $client = \Config\Services::curlrequest();
        $response = $client->get($url);
        return json_decode($response->getBody(), true);
    }

    private function filterSuitableSlots($weatherData)
    {
        $suitableSlots = [];
        foreach ($weatherData['data'] as $forecast) {
            // Check for favorable weather conditions
            if (in_array($forecast['weather_desc'], ['Clear', 'Cloudy'])) {
                $suitableSlots[] = $forecast['time_slot'];
            }
        }
        return $suitableSlots;
    }
}

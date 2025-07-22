<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Web routes (for serving the HTML frontend)
$routes->get('/', 'ActivityScheduler::index');
$routes->get('scheduler', 'ActivityScheduler::index');
$routes->get('bmkg_api', 'ActivityScheduler::bmkg_api');
$routes->get('dbtest', 'DBTest::index');

// API Routes for Activity Scheduler
$routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    
    // Weather forecast routes
    $routes->get('weather', 'Activity::getWeatherForecast');
    $routes->get('locations', 'Activity::getLocationOptions');
    
    // Activity management routes
    $routes->post('activities', 'Activity::scheduleActivity');
    $routes->get('activities', 'Activity::getActivities');
    $routes->get('activities/(:num)', 'Activity::show/$1');
    $routes->put('activities/(:num)', 'Activity::update/$1');
    $routes->delete('activities/(:num)', 'Activity::delete/$1');
    
    // Activity status management
    $routes->patch('activities/(:num)/status', 'Activity::updateStatus/$1');
    
    // Statistics and reporting
    $routes->get('activities/stats', 'Activity::getStatistics');
    $routes->get('activities/search', 'Activity::search');
    $routes->get('activities/upcoming', 'Activity::getUpcoming');
    $routes->get('activities/weather-summary', 'Activity::getWeatherSummary');
    
    // Activities by filters
    $routes->get('activities/location/(:alphanum)', 'Activity::getByLocation/$1');
    $routes->get('activities/status/(:alpha)', 'Activity::getByStatus/$1');
    $routes->get('activities/date-range', 'Activity::getByDateRange');
});

// CORS preflight requests
$routes->options('api/(.*)', function() {
    return service('response')->setStatusCode(200);
});
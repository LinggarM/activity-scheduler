<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('activity', 'ActivityController::index');
$routes->post('activity/schedule', 'ActivityController::schedule');

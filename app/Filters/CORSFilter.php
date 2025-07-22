<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CORSFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        
        // Allow from any origin
        $response->setHeader('Access-Control-Allow-Origin', '*');
        
        // Allow specific methods
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        
        // Allow specific headers
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        
        // Allow credentials
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        
        // Max age for preflight
        $response->setHeader('Access-Control-Max-Age', '86400');
        
        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            return $response->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Additional processing if needed
    }
}
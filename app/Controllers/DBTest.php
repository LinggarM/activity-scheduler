<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class DBTest extends Controller
{
    public function index()
    {
        // try {
        //     $db = Database::connect();
        //     if ($db->connID) {
        //         echo "✅ Database connection successful.";
        //     } else {
        //         echo "❌ Failed to connect to database.";
        //     }
        // } catch (\Throwable $e) {
        //     echo "❌ Error: " . $e->getMessage();
        // }
        $db = Database::connect();
        $builder = $db->table('activities');
        $data = $builder->get()->getResult();

        echo '<pre>',print_r($data, true),'</pre>';die;
    }
}

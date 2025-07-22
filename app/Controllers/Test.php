<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Test extends Controller
{
    public function dbtest()
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

        echo '<pre>', print_r($data, true), '</pre>';
        die;
    }

    public function csv_to_json()
    {
        $csvFile = WRITEPATH . 'uploads\files\wilayah.csv';
        $jsonFile = WRITEPATH . 'uploads\files\wilayah.json';

        $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];

        $prov = $kab = $kec = null;

        foreach ($lines as $line) {
            [$kode, $nama] = explode(',', $line);
            $level = substr_count($kode, '.');

            if ($level == 0) {
                $prov = $nama;
            } elseif ($level == 1) {
                $kab = $nama;
            } elseif ($level == 2) {
                $kec = $nama;
            } elseif ($level == 3) {
                $data[] = [
                    'id' => $kode,
                    'text' => "$nama, $kec, $kab, $prov"
                ];
            }
        }

        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "JSON file created.\n";
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivitiesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'activity_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'location_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => false,
                'comment'    => 'BMKG location code (Kode Wilayah Tingkat IV)',
            ],
            'location_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'preferred_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'selected_datetime' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'weather_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
                'comment'    => 'Weather condition in Indonesian',
            ],
            'weather_condition_en' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Weather condition in English',
            ],
            'temperature' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Temperature in Celsius',
            ],
            'humidity' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Humidity percentage',
            ],
            'wind_speed' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'null'       => true,
                'comment'    => 'Wind speed in km/h',
            ],
            'wind_direction' => [
                'type'       => 'VARCHAR',
                'constraint' => '3',
                'null'       => true,
                'comment'    => 'Wind direction (N, NE, E, etc.)',
            ],
            'cloud_coverage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Cloud coverage percentage',
            ],
            'visibility' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'Visibility range',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'completed', 'cancelled'],
                'default'    => 'scheduled',
                'null'       => false,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);
        
        // Indexes for better query performance
        $this->forge->addKey('location_code');
        $this->forge->addKey('preferred_date');
        $this->forge->addKey('selected_datetime');
        $this->forge->addKey('status');
        $this->forge->addKey(['preferred_date', 'status']);
        $this->forge->addKey(['location_code', 'preferred_date']);

        // Create table
        $this->forge->createTable('activities');
    }

    public function down()
    {
        $this->forge->dropTable('activities');
    }
}
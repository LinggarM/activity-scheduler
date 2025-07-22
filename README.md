# Outdoor Activity Scheduler

A web application that helps users schedule outdoor activities by analyzing weather forecasts from BMKG (Badan Meteorologi, Klimatologi, dan Geofisika) API to suggest optimal time slots with favorable weather conditions.

## Features

### Frontend
- 📱 Responsive web interface
- 🌤️ Interactive weather forecast display
- ⏰ Time slot selection with weather conditions
- 📊 Real-time weather data visualization
- ✨ Modern UI with smooth animations

### Backend
- 🚀 Built with CodeIgniter 4 framework
- 🌐 RESTful API endpoints
- 🏛️ Integration with BMKG Weather Forecast API
- 💾 Local database storage for activities
- 🔍 Smart weather filtering for outdoor activities
- 📈 Activity statistics and reporting

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 8.1+, CodeIgniter 4
- **Database**: MySQL 8.0+
- **API**: BMKG Weather Forecast API
- **Styling**: Custom CSS with modern design principles

## Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Web server (Apache/Nginx) or PHP built-in server

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd outdoor-activity-scheduler
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Database Setup

Create a MySQL database:

```sql
CREATE DATABASE activity_scheduler;
```

Copy the environment file and configure database settings:

```bash
cp env .env
```

Edit `.env` file with your database credentials:

```env
database.default.hostname = localhost
database.default.database = activity_scheduler
database.default.username = your_username
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 4. Run Database Migrations

```bash
php spark migrate
```

### 5. Start the Development Server

```bash
php spark serve
```

The application will be available at `http://localhost:8080`

## API Endpoints

### Weather Endpoints

#### Get Weather Forecast
```http
GET /api/weather?location={location_code}
```

**Parameters:**
- `location` - BMKG location code (Kode Wilayah Tingkat IV)

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "utc_datetime": "2025-07-21 12:00:00",
      "local_datetime": "2025-07-21 19:00:00",
      "t": 28.5,
      "hu": 75,
      "weather_desc": "Berawan",
      "weather_desc_en": "Cloudy",
      "ws": 12,
      "wd": "NE",
      "tcc": 60,
      "vs_text": "> 10"
    }
  ],
  "source": "BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)"
}
```

#### Get Location Options
```http
GET /api/locations
```

### Activity Endpoints

#### Schedule New Activity
```http
POST /api/activities
```

**Request Body:**
```json
{
  "activity_name": "Field Visit",
  "location": "3275010001",
  "preferred_date": "2025-07-22",
  "selected_datetime": "2025-07-22 09:00:00",
  "weather_condition": "Cerah",
  "temperature": 28.5,
  "humidity": 65
}
```

#### Get All Activities
```http
GET /api/activities
```

#### Get Activity Statistics
```http
GET /api/activities/stats
```

#### Get Upcoming Activities
```http
GET /api/activities/upcoming
```

#### Search Activities
```http
GET /api/activities/search?q={keyword}
```

## File Structure

```
outdoor-activity-scheduler/
├── app/
│   ├── Controllers/
│   │   └── Activity.php          # Main API controller
│   ├── Models/
│   │   └── ActivityModel.php     # Activity data model
│   ├── Database/
│   │   └── Migrations/
│   │       └── CreateActivitiesTable.php
│   ├── Filters/
│   │   └── CORSFilter.php        # CORS handling
│   └── Config/
│       └── Routes.php            # API routes
├── public/
│   └── index.php                 # Entry point
├── frontend/
│   └── index.html               # Frontend interface
├── .env                         # Environment configuration
├── composer.json               # PHP dependencies
└── README.md                   # This file
```

## Database Schema

### Activities Table

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary key |
| activity_name | VARCHAR(255) | Name of the activity |
| location_code | VARCHAR(10) | BMKG location code |
| location_name | VARCHAR(255) | Human-readable location name |
| preferred_date | DATE | Preferred date for activity |
| selected_datetime | DATETIME | Selected date and time |
| weather_condition | VARCHAR(100) | Weather condition (Indonesian) |
| weather_condition_en | VARCHAR(100) | Weather condition (English) |
| temperature | DECIMAL(5,2) | Temperature in Celsius |
| humidity | DECIMAL(5,2) | Humidity percentage |
| wind_speed | DECIMAL(6,2) | Wind speed in km/h |
| wind_direction | VARCHAR(3) | Wind direction |
| cloud_coverage | DECIMAL(5,2) | Cloud coverage percentage |
| visibility | VARCHAR(20) | Visibility range |
| status | ENUM | scheduled, completed, cancelled |
| notes | TEXT | Additional notes |
| created_at | DATETIME | Record creation time |
| updated_at | DATETIME | Record update time |

## BMKG API Integration

The application integrates with the BMKG (Indonesian Meteorological Agency) public weather forecast API:

- **Base URL**: `https://api.bmkg.go.id/publik/prakiraan-cuaca`
- **Format**: JSON
- **Coverage**: 3-day forecast with 8 predictions per day (every 3 hours)
- **Rate Limit**: 60 requests per minute per IP
- **Location Codes**: Uses Indonesian administrative code level IV from Ministry of Home Affairs

### Weather Filtering Logic

The application considers the following weather conditions suitable for outdoor activities:
- ✅ **Suitable**: Cerah (Clear), Berawan (Cloudy), Berawan Sebagian (Partly Cloudy)
- ❌ **Not Suitable**: Any
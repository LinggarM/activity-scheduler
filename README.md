# 🌤️ Outdoor Activity Scheduler

A CodeIgniter 4 web application that helps you plan outdoor activities based on weather forecasts from BMKG (Badan Meteorologi, Klimatologi, dan Geofisika) Indonesia.

## 📋 Features

- **Weather Integration**: Real-time weather data from BMKG API
- **Smart Scheduling**: Automatically suggests optimal time slots based on weather conditions
- **Location Search**: Search and select from Indonesian cities/districts
- **Activity Management**: Create, view, update, and manage scheduled activities
- **Weather Filtering**: Filters out unsuitable weather conditions (rain, storms)
- **Statistics Dashboard**: View activity statistics and weather summaries
- **Responsive Design**: Mobile-friendly interface
- **RESTful API**: Complete API endpoints for all operations

## 🛠️ Tech Stack

- **Backend**: CodeIgniter 4
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **API Integration**: BMKG Weather API
- **UI Components**: Select2 for location search
- **Styling**: Custom CSS with gradient backgrounds

## 📦 Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd activity-scheduler
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   cp env .env
   ```

4. **Configure database in `.env`**
   ```env
   database.default.hostname = localhost
   database.default.database = db_activity_scheduler
   database.default.username = root
   database.default.password = 
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

5. **Set application URL**
   ```env
   app.baseURL = 'http://localhost/activity-scheduler/'
   CI_ENVIRONMENT = development
   ```

6. **Create database**
   ```sql
   CREATE DATABASE db_activity_scheduler;
   ```

7. **Run migrations**
   ```bash
   php spark migrate
   ```

8. **Set permissions** (Linux/Mac)
   ```bash
   chmod -R 777 writable/
   ```

9. **Start development server**
   ```bash
   php spark serve
   ```

   Or configure your web server to point to the `public/` directory.

## 🗄️ Database Schema

### Activities Table

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary key |
| activity_name | VARCHAR(255) | Name of the activity |
| location_code | VARCHAR(20) | BMKG location code |
| location_name | VARCHAR(255) | Human-readable location name |
| preferred_date | DATE | User's preferred date |
| selected_datetime | DATETIME | Final selected date and time |
| weather_condition | VARCHAR(100) | Weather condition (Indonesian) |
| weather_condition_en | VARCHAR(100) | Weather condition (English) |
| temperature | DECIMAL(5,2) | Temperature in Celsius |
| humidity | DECIMAL(5,2) | Humidity percentage |
| wind_speed | DECIMAL(6,2) | Wind speed in km/h |
| wind_direction | VARCHAR(3) | Wind direction |
| cloud_coverage | DECIMAL(5,2) | Cloud coverage percentage |
| visibility | VARCHAR(20) | Visibility range |
| status | ENUM | scheduled/completed/cancelled |
| notes | TEXT | Additional notes |
| created_at | DATETIME | Creation timestamp |
| updated_at | DATETIME | Last update timestamp |

## 🚀 API Endpoints

### Weather Endpoints
- `GET /api/weather` - Get weather forecast
- `GET /api/locations` - Get location options
- `GET /api/search-location` - Search locations

### Activity Management
- `POST /api/activities` - Schedule new activity
- `GET /api/activities` - Get all activities
- `GET /api/activities/{id}` - Get specific activity
- `PUT /api/activities/{id}` - Update activity
- `DELETE /api/activities/{id}` - Delete activity
- `PATCH /api/activities/{id}/status` - Update activity status

### Analytics & Reporting
- `GET /api/activities/stats` - Get activity statistics
- `GET /api/activities/search` - Search activities
- `GET /api/activities/upcoming` - Get upcoming activities
- `GET /api/activities/weather-summary` - Weather summary
- `GET /api/activities/location/{code}` - Activities by location
- `GET /api/activities/status/{status}` - Activities by status
- `GET /api/activities/date-range` - Activities by date range

## 🌐 Usage

### Web Interface

1. **Access the application** at `http://localhost:8080` (or your configured URL)

2. **Schedule Activity**:
   - Enter activity name
   - Search and select location
   - Choose preferred date
   - Click "Find Optimal Time Slots"

3. **Select Time Slot**:
   - Review weather-based suggestions
   - Click on preferred time slot
   - Confirm selection

### API Usage

**Schedule Activity Example:**
```javascript
fetch('/api/activities', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        activityName: "Field Survey",
        location: "3171010001",
        locationTitle: "Jakarta Pusat - Gambir",
        preferredDate: "2024-12-25",
        selectedTime: "2024-12-25 10:00:00",
        weatherCondition: "Cerah",
        weatherConditionEn: "Clear",
        temperature: 28,
        humidity: 65,
        windSpeed: 12,
        windDirection: "NE",
        cloudCoverage: 20,
        visibility: "> 10"
    })
});
```

## ⚙️ Configuration

### BMKG API Settings
```env
bmkg.api.baseUrl = 'https://api.bmkg.go.id'
bmkg.api.timeout = 30
bmkg.api.userAgent = 'Activity-Scheduler/1.0'
```

### Weather Cache
```env
weather.cache.duration = 1800  # 30 minutes
```

### Activity Limits
```env
activity.max.perDay = 10
app.timezone = 'Asia/Jakarta'
```

## 🔧 Customization

### Adding New Locations

Edit the `getLocationOptions()` method in `Controller/Activity.php`:

```php
$locations = [
    ['code' => 'LOCATION_CODE', 'name' => 'Location Name'],
    // Add more locations
];
```

### Weather Condition Filtering

Modify the `filterSuitableWeather()` method to adjust weather criteria:

```php
$suitableConditions = ['cerah', 'clear', 'berawan', 'cloudy'];
$rainKeywords = ['hujan', 'rain', 'storm'];
```

### Styling

Customize the appearance by editing `public/css/style.css`:
- Change gradient colors
- Modify component styles
- Adjust responsive breakpoints

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `.env`
   - Ensure MySQL service is running
   - Verify database exists

2. **BMKG API Timeout**
   - Check internet connection
   - API may be temporarily unavailable
   - Falls back to mock data automatically

3. **Location Search Not Working**
   - Ensure `wilayah.json` file exists in `writable/uploads/files/`
   - Check file permissions

4. **CSS/JS Not Loading**
   - Verify `app.baseURL` in `.env`
   - Check web server configuration
   - Clear browser cache

### Debug Mode

Enable debugging in `.env`:
```env
CI_ENVIRONMENT = development
app.CSRFProtection = false  # For API testing
logger.threshold = 4        # Debug level logging
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **BMKG** - For providing weather data API
- **CodeIgniter Team** - For the excellent framework
- **Select2** - For the location search component

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the [CodeIgniter 4 Documentation](https://codeigniter.com/user_guide/)
- Review BMKG API documentation

---

**Made with ❤️ for better outdoor activity planning in Indonesia** 🇮🇩
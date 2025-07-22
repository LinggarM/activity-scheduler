<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outdoor Activity Scheduler</title>
    <link rel="icon" href="<?= base_url('public/images/favicon.ico') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= base_url('public/css/style.css') ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌤️ Outdoor Activity Scheduler</h1>
            <p>Plan your outdoor activities with weather forecasts from BMKG</p>
        </div>

        <div class="main-content">
            <div class="form-section">
                <h2>Schedule Activity</h2>
                <form id="activityForm">
                    <div class="form-group">
                        <label for="activityName">Activity Name</label>
                        <input type="text" id="activityName" name="activityName" placeholder="e.g., Field Visit, Maintenance Task" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <select id="location" name="location" required>
                            <option value="">Select Location...</option>
                            <option value="3171010001">Jakarta Pusat - Gambir</option>
                            <option value="3275010001">Bogor - Bogor Tengah</option>
                            <option value="3276010001">Depok - Pancoran Mas</option>
                            <option value="3171020001">Jakarta Pusat - Tanah Abang</option>
                            <option value="3374010001">Sleman - Depok</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="preferredDate">Preferred Date</label>
                        <input type="date" id="preferredDate" name="preferredDate" required>
                    </div>

                    <button type="submit" class="btn" id="submitBtn">
                        🔍 Find Optimal Time Slots
                    </button>
                </form>

                <div class="loading" id="loading">
                    <p>🌦️ Fetching weather forecast...</p>
                </div>

                <div class="success-message" id="successMessage"></div>
                <div class="error-message" id="errorMessage"></div>
            </div>

            <div class="results-section">
                <h2>Weather Suggestions</h2>
                <div id="weatherResults">
                    <p style="color: #888; text-align: center; padding: 40px 0;">
                        📅 Enter activity details to see weather-based time slot suggestions
                    </p>
                </div>

                <div class="weather-suggestions" id="weatherSuggestions">
                    <div id="timeSlots"></div>
                    <button type="button" class="btn" id="confirmBtn" style="margin-top: 20px; display: none;">
                        ✅ Confirm Selected Time Slot
                    </button>
                </div>
            </div>
        </div>

        <div class="bmkg-credit">
            <p>Weather data provided by <strong>BMKG</strong> (Badan Meteorologi, Klimatologi, dan Geofisika)</p>
        </div>
    </div>

    <script>
        let selectedTimeSlot = null;
        let currentActivityData = null;

        // Set minimum date to today
        document.getElementById('preferredDate').min = new Date().toISOString().split('T')[0];

        // Mock BMKG API response for demonstration
        function getMockWeatherData() {
            const now = new Date();
            const mockData = [];
            
            for (let i = 0; i < 24; i++) { // 3 days * 8 forecasts per day
                const forecastTime = new Date(now.getTime() + (i * 3 * 60 * 60 * 1000));
                const weatherConditions = [
                    { desc: 'Cerah', desc_en: 'Clear', icon: '☀️', suitable: true },
                    { desc: 'Berawan', desc_en: 'Cloudy', icon: '☁️', suitable: true },
                    { desc: 'Berawan Sebagian', desc_en: 'Partly Cloudy', icon: '⛅', suitable: true },
                    { desc: 'Hujan Ringan', desc_en: 'Light Rain', icon: '🌦️', suitable: false },
                    { desc: 'Hujan Sedang', desc_en: 'Moderate Rain', icon: '🌧️', suitable: false },
                    { desc: 'Hujan Lebat', desc_en: 'Heavy Rain', icon: '⛈️', suitable: false }
                ];
                
                const weather = weatherConditions[Math.floor(Math.random() * weatherConditions.length)];
                
                mockData.push({
                    utc_datetime: forecastTime.toISOString().replace('T', ' ').substring(0, 19),
                    local_datetime: forecastTime.toISOString().replace('T', ' ').substring(0, 19),
                    t: Math.floor(Math.random() * 10) + 25, // 25-35°C
                    hu: Math.floor(Math.random() * 30) + 60, // 60-90%
                    weather_desc: weather.desc,
                    weather_desc_en: weather.desc_en,
                    ws: Math.floor(Math.random() * 15) + 5, // 5-20 km/h
                    wd: ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][Math.floor(Math.random() * 8)],
                    tcc: Math.floor(Math.random() * 100),
                    vs_text: '> 10',
                    icon: weather.icon,
                    suitable: weather.suitable
                });
            }
            
            return { data: mockData };
        }

        function getWeatherIcon(description) {
            const icons = {
                'cerah': '☀️',
                'clear': '☀️',
                'berawan': '☁️',
                'cloudy': '☁️',
                'partly cloudy': '⛅',
                'berawan sebagian': '⛅',
                'hujan': '🌧️',
                'rain': '🌧️',
                'hujan ringan': '🌦️',
                'light rain': '🌦️',
                'hujan lebat': '⛈️',
                'heavy rain': '⛈️'
            };
            
            return icons[description.toLowerCase()] || '🌤️';
        }

        function isSuitableWeather(weatherDesc) {
            const unsuitable = ['hujan', 'rain', 'storm', 'thunderstorm'];
            return !unsuitable.some(word => weatherDesc.toLowerCase().includes(word));
        }

        function displayWeatherSuggestions(weatherData, selectedDate) {
            const timeSlots = document.getElementById('timeSlots');
            const weatherSuggestions = document.getElementById('weatherSuggestions');
            const weatherResults = document.getElementById('weatherResults');
            
            // Filter for selected date and suitable weather
            const selectedDateStr = selectedDate;
            const suitableSlots = weatherData.data.filter(item => {
                const itemDate = item.local_datetime.split(' ')[0];
                return itemDate === selectedDateStr && isSuitableWeather(item.weather_desc);
            });

            if (suitableSlots.length === 0) {
                weatherResults.innerHTML = `
                    <div style="text-align: center; padding: 40px 0; color: #e74c3c;">
                        <h3>⚠️ No Suitable Weather Conditions</h3>
                        <p>Unfortunately, no optimal time slots were found for ${selectedDate}. Consider selecting a different date.</p>
                    </div>
                `;
                weatherSuggestions.style.display = 'none';
                return;
            }

            weatherResults.style.display = 'none';
            weatherSuggestions.style.display = 'block';
            
            timeSlots.innerHTML = '';
            
            suitableSlots.forEach((slot, index) => {
                const timeSlotEl = document.createElement('div');
                timeSlotEl.className = 'time-slot';
                timeSlotEl.dataset.index = index;
                
                const time = slot.local_datetime.split(' ')[1].substring(0, 5);
                const icon = getWeatherIcon(slot.weather_desc);
                
                timeSlotEl.innerHTML = `
                    <div class="time-slot-header">
                        <div class="time-slot-time">${time}</div>
                        <div class="weather-icon">${icon}</div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>${slot.weather_desc}</strong>
                    </div>
                    <div class="time-slot-details">
                        <div class="weather-param">🌡️ ${slot.t}°C</div>
                        <div class="weather-param">💧 ${slot.hu}% Humidity</div>
                        <div class="weather-param">💨 ${slot.ws} km/h</div>
                        <div class="weather-param">☁️ ${slot.tcc}% Cloud</div>
                    </div>
                `;
                
                timeSlotEl.addEventListener('click', () => selectTimeSlot(timeSlotEl, slot));
                timeSlots.appendChild(timeSlotEl);
            });
        }

        function selectTimeSlot(element, slotData) {
            // Remove previous selection
            document.querySelectorAll('.time-slot.selected').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Select current
            element.classList.add('selected');
            selectedTimeSlot = slotData;
            
            // Show confirm button
            document.getElementById('confirmBtn').style.display = 'block';
        }

        document.getElementById('activityForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const activityName = formData.get('activityName');
            const location = formData.get('location');
            const preferredDate = formData.get('preferredDate');
            
            currentActivityData = { activityName, location, preferredDate };
            
            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';
            
            try {
                // Simulate API call delay
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // In real implementation, this would call the BMKG API
                // const response = await fetch(`https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${location}`);
                // const weatherData = await response.json();
                
                const weatherData = getMockWeatherData();
                displayWeatherSuggestions(weatherData, preferredDate);
                
            } catch (error) {
                document.getElementById('errorMessage').textContent = 'Failed to fetch weather data. Please try again.';
                document.getElementById('errorMessage').style.display = 'block';
            } finally {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
            }
        });

        document.getElementById('confirmBtn').addEventListener('click', async function() {
            if (!selectedTimeSlot || !currentActivityData) return;
            
            this.disabled = true;
            this.textContent = '⏳ Saving...';
            
            try {
                // Simulate saving to database
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // In real implementation, this would send data to CodeIgniter backend
                const activityData = {
                    ...currentActivityData,
                    selectedTime: selectedTimeSlot.local_datetime,
                    weatherCondition: selectedTimeSlot.weather_desc,
                    temperature: selectedTimeSlot.t,
                    humidity: selectedTimeSlot.hu
                };
                
                document.getElementById('successMessage').innerHTML = `
                    <strong>✅ Activity Scheduled Successfully!</strong><br>
                    <strong>${currentActivityData.activityName}</strong> has been scheduled for 
                    <strong>${selectedTimeSlot.local_datetime}</strong> with <strong>${selectedTimeSlot.weather_desc}</strong> conditions.
                `;
                document.getElementById('successMessage').style.display = 'block';
                
                // Reset form and selections
                document.getElementById('activityForm').reset();
                document.getElementById('preferredDate').min = new Date().toISOString().split('T')[0];
                selectedTimeSlot = null;
                currentActivityData = null;
                document.getElementById('weatherSuggestions').style.display = 'none';
                document.getElementById('weatherResults').innerHTML = `
                    <p style="color: #888; text-align: center; padding: 40px 0;">
                        📅 Enter activity details to see weather-based time slot suggestions
                    </p>
                `;
                document.getElementById('weatherResults').style.display = 'block';
                
            } catch (error) {
                document.getElementById('errorMessage').textContent = 'Failed to save activity. Please try again.';
                document.getElementById('errorMessage').style.display = 'block';
            } finally {
                this.disabled = false;
                this.textContent = '✅ Confirm Selected Time Slot';
                this.style.display = 'none';
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outdoor Activity Scheduler</title>
    <link rel="icon" href="<?= base_url('public/images/favicon.ico') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= base_url('public/css/style.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let selectedTimeSlot = null;
        let currentActivityData = null;

        // Location select2 initialization
        $('#location').select2({
            placeholder: 'Find location...',
            ajax: {
                url: '<?= base_url('api/search-location') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data
                }),
                cache: true
            },
            minimumInputLength: 2,
            width: '100%', // Make it full width
            dropdownAutoWidth: true,
            theme: 'default' // This ensures we're using the default theme we can override
        });

        // Set minimum date to today
        document.getElementById('preferredDate').min = new Date().toISOString().split('T')[0];

        function displayWeatherSuggestions(weatherData, selectedDate) {
            const timeSlots = document.getElementById('timeSlots');
            const weatherSuggestions = document.getElementById('weatherSuggestions');
            const weatherResults = document.getElementById('weatherResults');

            if (weatherData.data.length === 0) {
                weatherResults.innerHTML = `
                    <div style="text-align: center; padding: 40px 0; color: #e74c3c;">
                        <h3>⚠️ No Suitable Weather Conditions</h3>
                        <p>Unfortunately, no optimal time slots were found for ${selectedDate}. Consider selecting a different date.</p>
                    </div>
                `;
                weatherResults.style.display = 'block';
                weatherSuggestions.style.display = 'none';
                return;
            }

            weatherResults.style.display = 'none';
            weatherSuggestions.style.display = 'block';

            timeSlots.innerHTML = '';

            weatherData.data.forEach((slot, index) => {
                const timeSlotEl = document.createElement('div');
                timeSlotEl.className = 'time-slot';
                timeSlotEl.dataset.index = index;

                const time = slot.local_datetime.split(' ')[1].substring(0, 5);
                const icon = `<img src="${slot.image}" alt="${slot.weather_desc_en}" style="width: 32px; height: 32px;">`;

                timeSlotEl.innerHTML = `
                    <div class="time-slot-header">
                        <div class="time-slot-time">${time}</div>
                        <div class="weather-icon">${icon}</div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>${slot.weather_desc_en}</strong>
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

            // Get selected text from select2
            const locationSelect = $('#location').select2('data')[0];
            const locationTitle = locationSelect ? locationSelect.text : '';

            currentActivityData = {
                activityName,
                location,
                locationTitle,
                preferredDate
            };

            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';

            try {
                const baseUrl = "<?= base_url('api/weather') ?>";
                const response = await fetch(`${baseUrl}?location=${encodeURIComponent(location)}&date=${preferredDate}`);

                if (!response.ok) {
                    throw new Error('Weather API error');
                }

                const weatherData = await response.json();

                if (weatherData.status === 'success') {
                    displayWeatherSuggestions(weatherData, preferredDate);
                } else {
                    throw new Error('Weather API returned an error');
                }

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
                    weatherConditionEn: selectedTimeSlot.weather_desc_en,
                    temperature: selectedTimeSlot.t,
                    humidity: selectedTimeSlot.hu,
                    windSpeed: selectedTimeSlot.ws,
                    windDirection: selectedTimeSlot.wd,
                    cloudCoverage: selectedTimeSlot.tcc,
                    visibility: selectedTimeSlot.vs_text,
                };

                // Kirim ke backend (pastikan endpoint ini benar dan bisa menerima POST JSON)
                const response = await fetch('<?= base_url('api/activities') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(activityData)
                });

                if (!response.ok) throw new Error('Network error');

                const result = await response.json();

                if (result.status !== 'success') throw new Error(result.message || 'Unknown error');

                document.getElementById('successMessage').innerHTML = `
                    <strong>✅ Activity Scheduled Successfully!</strong><br>
                    <strong>${currentActivityData.activityName}</strong> has been scheduled for 
                    <strong>${selectedTimeSlot.local_datetime}</strong> with <strong>${selectedTimeSlot.weather_desc_en}</strong> conditions.
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
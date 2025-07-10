document.getElementById("search").addEventListener("click", getWeatherData);

async function getWeatherData() {
    let cityName = document.getElementById("city").value;
    if (!cityName){
        cityName = "Montgomery";
    }

    let data;

    if (navigator.onLine) {
        try {
            
            const response = await fetch(`https://aryadev.free.nf/Prototype2/connection.php?q=${cityName}`);

            if (!response.ok) {
                throw new Error("City not found or API error");
            }

            const result = await response.json();

            
            data = result[0];

        
            localStorage.setItem(cityName, JSON.stringify(data));
        } catch (error) {
            console.error("Error fetching weather data:", error);
            alert("Could not fetch weather data. Please check the city name and try again.");
            return;
        }
    } else {
        
        const storedData = localStorage.getItem(cityName);
        if (!storedData) {
            alert("No cached data available for this city.");
            return;
        }
        data = JSON.parse(storedData);
    }

    
    document.getElementById("country").innerHTML = cityName;
    document.getElementById("temperature").innerHTML = parseInt(data.temperature - 272.5) + '°c';
    document.getElementById("pressure").innerHTML = data.pressure + " hPa";
    document.getElementById("humidity").innerHTML = data.humidity + " %";
    document.getElementById("windSpeed").innerHTML = parseInt(data.wind) + " m/s";
    document.getElementById("weatherCondition").innerHTML = data.weather_condition;
    document.getElementById("weatherIcon").src = `http://openweathermap.org/img/wn/${data.weather_icon}@2x.png`;

    let windConvert = data.wind_deg;
    document.getElementById("windDirection").innerHTML = (() => {
        if (windConvert >= 0 && windConvert < 45) {
            return "North";
        } else if (windConvert >= 45 && windConvert < 90) {
            return "North East";
        } else if(windConvert >= 90 && windConvert < 135) {
            return "East";
        } else if (windConvert >= 135 && windConvert < 180) {
            return "South East";
        }else if(windConvert >= 180 && windConvert < 225) {
            return "South";
        } else if (windConvert >= 225 && windConvert < 270) {
            return "South West";
        } else if(windConvert >= 270 && windConvert < 315) {
            return "West";
        }else{
            return "North West";
        }
    })();

    
    const localTime = new Date();
    const hours = localTime.getHours().toString().padStart(2, '0');
    const minutes = localTime.getMinutes().toString().padStart(2, '0');
    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    document.getElementById("time").innerHTML = `${hours}:${minutes}`;
    document.getElementById("day").innerHTML = days[localTime.getDay()];
}

document.addEventListener("DOMContentLoaded", () => getWeatherData());

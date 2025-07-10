<?php
header("Access-Control-Allow-Origin: *");
$conn = mysqli_connect("sql210.infinityfree.com", "if0_38968064", "CiThmffw19DwM");
// if($conn){
//     // echo "Connection Successful <br>";
// }
// else{
//     echo "Failed to connect".mysqli_connect_error();
// }

// $createDatabase = "CREATE DATABASE IF NOT EXISTS prototype2";
// if (mysqli_query($conn, $createDatabase)) {
//     // echo "Database Created or already Exists <br>";
// } else {
//     // echo "Failed to create database <br>" . mysqli_connect_error();
// }
mysqli_select_db($conn, 'if0_38968064_weather');
$createTable = "CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT NOT NULL,
    city VARCHAR(100),
    humidity FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    wind_deg INT NOT NULL,
    pressure FLOAT NOT NULL,
    temperature FLOAT NOT NULL,
    weather_condition VARCHAR(100),
    weather_icon VARCHAR(100),
    date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id)
);";

mysqli_query($conn, $createTable);

if(isset($_GET['q'])){
    $cityName = $_GET['q'];
}else{
    $cityName = "Montgomery";
}

// Check if data exists and is less than 2 hours old
$selectAllData = "SELECT * FROM weather WHERE city = '$cityName' ORDER BY date_added DESC LIMIT 1";
$result = mysqli_query($conn, $selectAllData);

$fetchNewData = true;

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $dateAdded = strtotime($row['date_added']);
    $now = time();
    $diff = ($now - $dateAdded) / 3600; 

    if ($diff < 2) {
        $fetchNewData = false;
        $rows[] = $row;
    }
}

if ($fetchNewData) {
    $apiKey = "36c9fffaae5e731cd96aa502a9e51d95";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cityName) . "&appid=$apiKey";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data && isset($data['main'])) {
        $humidity = $data['main']['humidity'];
        $wind = $data['wind']['speed'];
        $wind_deg = isset($data['wind']['deg']) ? $data['wind']['deg'] : 0;
        $pressure = isset($data['main']['pressure']) ? $data['main']['pressure'] : null;

        $temperature = $data['main']['temp'];
        $weather_condition = $data['weather'][0]['main'];
        $weather_icon = $data['weather'][0]['icon'];
        $insertData = "INSERT INTO weather (city, humidity, wind, wind_deg, pressure, temperature, weather_condition, weather_icon)
            VALUES ('$cityName', '$humidity', '$wind', '$wind_deg', '$pressure', '$temperature', '$weather_condition', '$weather_icon')";

        if (mysqli_query($conn, $insertData)) {

            $result = mysqli_query($conn, $selectAllData);
            $rows = [];
            while($row = mysqli_fetch_assoc($result)){
                $rows[] = $row;
            }
        } else {

            http_response_code(500);
            echo json_encode(["error" => "Failed to insert data: " . mysqli_error($conn)]);
            exit;
        }
    } else {

        http_response_code(404);
        echo json_encode(["error" => "City not found or API error"]);
        exit;
    }
}


header('Content-Type: application/json');
echo json_encode($rows);

?>
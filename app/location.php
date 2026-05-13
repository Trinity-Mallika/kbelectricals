<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get latitude and longitude sent from JavaScript
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Your Google API key
    $apiKey = 'AIzaSyD60TsOPfBQDMpiGwEWusBT-UBUUM6Y8O8';

    // Geocoding API URL
    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";

    // Fetch geocoding data
    $geocodeData = json_decode(file_get_contents($geocodeUrl));

    if ($geocodeData->status == 'OK') {
        // Return the formatted address as a JSON response
        echo json_encode([
            'address' => $geocodeData->results[0]->formatted_address,
            'latitude' =>  $latitude,
            'longitude' =>  $longitude,
        ]);
    } else {
        // If there's an error, return a message
        echo json_encode(['error' => 'Unable to retrieve address.']);
    }
}

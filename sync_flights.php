<?php
include 'db_connect.php';

// OpenSky Network OAuth2 credentials
$clientId     = "juanvaldezmegatr0n-api-client";
$clientSecret = "3TOkLFrBb3wpK9YyWLUiqRfTpSl1mVbK";

// OAuth2 Bearer Token
$tokenUrl = "https://auth.opensky-network.org/auth/realms/opensky-network/protocol/openid-connect/token";

$tokenContext = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ])
    ]
]);

$tokenResponse = file_get_contents($tokenUrl, false, $tokenContext);
if (!$tokenResponse) {
    die("Error: Could not reach OpenSky authentication server. Check your credentials.");
}

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    die("Auth Error: " . print_r($tokenData, true));
}

$accessToken = $tokenData['access_token'];

// Fetch arrivals at MCO (KMCO)
// OpenSky only provides historical data (previous day or earlier)
$end   = strtotime("yesterday 23:59:59");
$begin = strtotime("yesterday 00:00:00");

// MCO's ICAO code is KMCO
$url = "https://opensky-network.org/api/flights/arrival?airport=KMCO&begin=$begin&end=$end";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $accessToken\r\n"
    ]
]);

$json = file_get_contents($url, false, $context);

if ($json === false) {
    die("Error: Could not fetch flight data from OpenSky. The airport may have no recorded arrivals for this period.");
}

$flights = json_decode($json, true);

if (!is_array($flights) || count($flights) === 0) {
    die("No arrivals found for MCO yesterday. Try a different date range.");
}

// Step 3: Insert flights into database
// OpenSky flight object fields:
//   icao24           - transponder hex address
//   firstSeen        - Unix timestamp of departure
//   estDepartureAirport - ICAO code of departure airport (can be null)
//   lastSeen         - Unix timestamp of arrival
//   estArrivalAirport   - ICAO code of arrival airport (can be null)
//   callsign         - flight callsign (can be null)

$insertedCount = 0;
$skippedCount  = 0;

foreach ($flights as $flight) {

    $callsign   = isset($flight['callsign']) ? trim($flight['callsign']) : null;
    $icao24     = $flight['icao24'] ?? null;
    $depIcao    = $flight['estDepartureAirport'] ?? 'UNKN';
    $arrIcao    = $flight['estArrivalAirport']   ?? 'KMCO';
    $firstSeen = isset($flight['firstSeen']) ? (int)$flight['firstSeen'] : 0;
    $lastSeen  = isset($flight['lastSeen'])  ? (int)$flight['lastSeen']  : 0;

    // Skip flights with no useful identifier
    if (!$callsign && !$icao24) {
        $skippedCount++;
        continue;
    }

    // Use callsign as flight number; fall back to icao24 transponder ID
    $flightNumber = $callsign ?: strtoupper($icao24);

    // Derive airline name from the first 3 letters of the callsign (IATA/ICAO prefix)
    // e.g. "DAL1234" -> "DAL", "AAL456" -> "AAL"
    // If no callsign, use the icao24 address as a stand-in
    if ($callsign && strlen($callsign) >= 3 && ctype_alpha(substr($callsign, 0, 3))) {
        $airlineCode = strtoupper(substr($callsign, 0, 3));
        $airlineName = $airlineCode . " Airlines"; // Simple readable label
    } else {
        $airlineName = "Unknown (" . strtoupper($icao24) . ")";
    }

    $airlineId = abs(crc32($airlineName));

    if ($firstSeen > 0 && $lastSeen > 0) {
        $depTime  = date("Y-m-d H:i:s", $firstSeen);
        $arrTime  = date("Y-m-d H:i:s", $lastSeen);
        $duration = (int)round(($lastSeen - $firstSeen) / 60);
    } else {
        $depTime  = date("Y-m-d H:i:s");
        $arrTime  = date("Y-m-d H:i:s");
        $duration = 0;
    }

    // Determine status based on arrival time vs now
    $status = (time() > $lastSeen) ? 'landed' : 'scheduled';

    // Ensure departure airport exists
    $stmtDep = $conn->prepare("
        INSERT IGNORE INTO Airports (airport_code, airport_name)
        VALUES (?, ?)
    ");
    $depName = $depIcao; // We only have the ICAO code from OpenSky
    $stmtDep->bind_param("ss", $depIcao, $depName);
    $stmtDep->execute();

    // Get departure airport_id
    $stmtDepId = $conn->prepare("SELECT airport_id FROM Airports WHERE airport_code = ?");
    $stmtDepId->bind_param("s", $depIcao);
    $stmtDepId->execute();
    $depResult = $stmtDepId->get_result()->fetch_assoc();
    $depAirportId = $depResult ? $depResult['airport_id'] : 1;

    // Arrival airport is always MCO (airport_id = 1 from seed)
    $arrAirportId = 1;

    // Insert airline
    $stmtAirline = $conn->prepare("
        INSERT IGNORE INTO Airlines (airline_id, airline_name)
        VALUES (?, ?)
    ");
    $stmtAirline->bind_param("is", $airlineId, $airlineName);
    $stmtAirline->execute();

    // Insert or update flight
    $stmtFlight = $conn->prepare("
        INSERT INTO Flights
            (flight_number, airline_id, departure_airport_id,
             arrival_airport_id, departure_time, arrival_time,
             flight_duration, gate_number, terminal, aircraft_type,
             is_delayed, status, passenger_count, seats_available)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, '', '', ?, 0, ?, 0, 0)
        ON DUPLICATE KEY UPDATE
            departure_time   = VALUES(departure_time),
            arrival_time     = VALUES(arrival_time),
            flight_duration  = VALUES(flight_duration),
            status           = VALUES(status)
    ");

    $aircraftType = strtoupper($icao24); // OpenSky doesn't provide aircraft type directly so 2nd database source necessary. 
    $stmtFlight->bind_param(
        "siiississ",   
        $flightNumber,   // s
        $airlineId,      // i
        $depAirportId,   // i
        $arrAirportId,   // i
        $depTime,        // s
        $arrTime,        // s
        $duration,       // i
        $aircraftType,   // s
        $status          // s
    );
    $stmtFlight->execute();
    $insertedCount++;

}

echo "Sync complete. Flights processed: $insertedCount. Skipped (no identifier): $skippedCount.";
?>
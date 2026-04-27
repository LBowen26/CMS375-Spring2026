<?php
include 'db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : "";
$searchLike = "%" . $search . "%";

$stmt = $conn->prepare("
SELECT
    f.flight_id,
    f.flight_number,
    a.airline_name,
    dep.airport_code AS departure_code,
    arr.airport_code AS arrival_code,
    f.departure_time,
    f.arrival_time,
    f.gate_number,
    f.terminal,
    f.aircraft_type,
    f.status,
    f.live_updated
FROM Flights f
JOIN Airlines a   ON f.airline_id           = a.airline_id
JOIN Airports dep ON f.departure_airport_id = dep.airport_id
JOIN Airports arr ON f.arrival_airport_id   = arr.airport_id
WHERE
    a.airline_name    LIKE ?
    OR dep.airport_code LIKE ?
    OR arr.airport_code LIKE ?
ORDER BY f.arrival_time ASC
");

$stmt->bind_param("sss", $searchLike, $searchLike, $searchLike);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Orlando Airport Flight Tracker</title>
<meta http-equiv="refresh" content="3600"><!-- refresh every hour; OpenSky data is historical -->
<style>
body {
    font-family: Arial;
    background: #f4f6f8;
    margin: 20px;
}
h1 {
    text-align: center;
}
.search-box {
    text-align: center;
    margin-bottom: 20px;
}
input {
    padding: 10px;
    width: 300px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}
th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #007BFF;
    color: white;
}
.delayed  { color: red;   font-weight: bold; }
.active   { color: blue;  font-weight: bold; }
.landed   { color: gray; }
.scheduled{ color: green; font-weight: bold; }
.updated  { text-align: center; margin-bottom: 15px; color: #555; font-size: 0.9em; }
.note     { text-align: center; margin-bottom: 10px; color: #888; font-size: 0.85em; }
</style>
</head>
<body>

<div style="text-align:right;margin-bottom:4px;">
    <a href="admin_login.php" style="font-size:0.82em;color:#007BFF;text-decoration:none;"> Admin Login</a>
</div>
<h1>Orlando Airport Flight Catalogue</h1>

<div class="updated">
    Last Page Load: <?php echo date("F j, Y g:i:s A"); ?>
</div>
<div class="note">
    Data sourced from OpenSky Network &mdash; arrivals reflect yesterday's flights (historical data).
    Run <a href="sync_flights.php">sync_flights.php</a> to refresh.
</div>

<div class="search-box">
    <form method="GET">
        <input type="text" name="search"
               placeholder="Search airline or airport code..."
               value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>
</div>

<table>
<tr>
    <th>Flight</th>
    <th>Airline</th>
    <th>From</th>
    <th>To</th>
    <th>Departure (UTC)</th>
    <th>Arrival (UTC)</th>
    <th>Duration (min)</th>
    <th>Gate</th>
    <th>Terminal</th>
    <th>Aircraft (ICAO)</th>
    <th>Status</th>
</tr>

<?php
$rowCount = 0;
while ($row = $result->fetch_assoc()) {
    $rowCount++;
    $class = "scheduled";
    if ($row['status'] == "delayed") $class = "delayed";
    if ($row['status'] == "active")  $class = "active";
    if ($row['status'] == "landed")  $class = "landed";

    $flightNum  = htmlspecialchars($row['flight_number']);
    $airline    = htmlspecialchars($row['airline_name']);
    $depCode    = htmlspecialchars($row['departure_code']);
    $arrCode    = htmlspecialchars($row['arrival_code']);
    $depTime    = htmlspecialchars($row['departure_time']);
    $arrTime    = htmlspecialchars($row['arrival_time']);
    $gate       = htmlspecialchars($row['gate_number']) ?: '—';
    $terminal   = htmlspecialchars($row['terminal'])    ?: '—';
    $aircraft   = htmlspecialchars($row['aircraft_type']) ?: '—';
    $status     = htmlspecialchars($row['status']);

    // Calculate duration from times if available
    $depTs  = strtotime($depTime);
    $arrTs  = strtotime($arrTime);
    $durMin = ($depTs && $arrTs && $arrTs > $depTs)
              ? round(($arrTs - $depTs) / 60)
              : '—';

    echo "
    <tr>
        <td><a href='flight_details.php?id={$row['flight_id']}'>$flightNum</a></td>
        <td>$airline</td>
        <td>$depCode</td>
        <td>$arrCode</td>
        <td>$depTime</td>
        <td>$arrTime</td>
        <td>$durMin</td>
        <td>$gate</td>
        <td>$terminal</td>
        <td>$aircraft</td>
        <td class='$class'>$status</td>
    </tr>";
}

if ($rowCount === 0) {
    echo "<tr><td colspan='11' style='padding:20px;color:#888;'>
          No flights found. Try running sync_flights.php first.
          </td></tr>";
}
?>
</table>

</body>
</html>
<?php
include 'db_connect.php';

$flight_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* Flight Info */
$stmt = $conn->prepare("
SELECT 
    f.flight_id,
    f.flight_number,
    f.status,
    a.airline_name,
    f.departure_time,
    f.arrival_time,
    f.gate_number,
    f.terminal,
    f.aircraft_type
FROM Flights f
JOIN Airlines a ON f.airline_id = a.airline_id
WHERE f.flight_id = ?
");

$stmt->bind_param("i", $flight_id);
$stmt->execute();
$result = $stmt->get_result();
$flight = $result->fetch_assoc();
if (!$flight) {
    echo "<p>Flight not found.</p><a href='index.php'>← Back</a>";
    exit;
}

/* Ticket Prices */
$stmt2 = $conn->prepare("
SELECT section_name, ticket_price
FROM TicketPrices
WHERE flight_id = ?
");

$stmt2->bind_param("i", $flight_id);
$stmt2->execute();
$prices = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Flight Details</title>
<style>
body{
    font-family: Arial;
    margin: 30px;
}
table{
    border-collapse: collapse;
    width: 500px;
}
th, td{
    border:1px solid #ddd;
    padding:10px;
}
th{
    background:#007BFF;
    color:white;
}
</style>
</head>
<body>

<h2>
Flight <?php echo $flight['flight_number']; ?>
</h2>

<p><strong>Airline:</strong> <?php echo $flight['airline_name']; ?></p>
<p><strong>Status:</strong> <?php echo $flight['status']; ?></p>
<p><strong>Departure:</strong> <?php echo $flight['departure_time']; ?></p>
<p><strong>Arrival:</strong> <?php echo $flight['arrival_time']; ?></p>
<p><strong>Gate:</strong> <?php echo $flight['gate_number']; ?></p>
<p><strong>Terminal:</strong> <?php echo $flight['terminal']; ?></p>
<p><strong>Aircraft:</strong> <?php echo $flight['aircraft_type']; ?></p>

<h3>Ticket Prices</h3>

<table>
<tr>
<th>Class</th>
<th>Price ($)</th>
</tr>

<?php
while($row = $prices->fetch_assoc()) {
    echo "<tr>
            <td>{$row['section_name']}</td>
            <td>{$row['ticket_price']}</td>
          </tr>";
}
?>

</table>

<br>
<a href="index.php">← Back to Flights</a>

</body>
</html>
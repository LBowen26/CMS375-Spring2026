<?php
include 'db_connect.php';

$flight_id = $_GET['id'];

$sql = "
Select f.flight_id, a.airline_name
From Flights f
Join Airlines a ON f.airline_id = a.airline_id
Where f.flight_id = $flight_id";

$result = $conn-> query($sql);
$flight = $result->fetch_assoc();

// ticket prices
$prices = $conn-> query ("
	Select section_name, ticket_price
	From TicketPrices
	Where flight_id = $flight_id");
?>

<h2>Flight <?php echo $flight['flight_id']; ?> - <?php echo $flight['airline_name']; ?></h2>

<table border = "1">
	<tr><th>Class</th><th>Price ($)</th></tr>

<?php 
while($row = $prices-> fetch_assoc()) {
	echo "<tr>
		<td>{$row['section_name']}</td>
		<td>{$row['ticket_price']}</td>
	</tr>";
}
?>

</table>

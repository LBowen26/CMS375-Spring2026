<?php
include 'db_connect.php';

//filter function
$search = "";
if (isset($_GET['search'])) {
	$search = $conn-> real_escape_string($_GET['search']);
}

// Query
$sql = "
Select
	f.flight_id,
	a.airline_name,
	dep.airport_code AS departure_code,
	arr.airport_code AS arrival_code,
	f.departure_time,
	f.arrival_time,
	f.gate_number,
	f.terminal,
	f.aircraft_type,
	f.is_delayed
From Flights f
Join Airlines a ON f.airline_id = a.airline_id
Join Airports dep ON f.departure_airport_id = dep.airport_id
Join Airports arr ON f.arrival_airport_id = arr.airport_id
Where a.airline_name Like '%$search%'
	Or dep.airport_code Like '%$search%'
	Or arr.airport_code Like '%$search%'
Order by f.departure_time ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Orlando Airport Flight Tracker</title>
	<style>
		body{
			font-family: Arial;
			background-color: #f4f6f8;
			margin 20px;
		}
		h1 {
			text-align: center;
		}

		.search-box {
			text-align: center;
			margin-bottom: 20px;
		}
		input[type="text"] {
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
			background-color: #007BFF;
			color: white;
		}
		.delayed {
			color: red;
			font-weight: bold;
		}
		.on-time {
			color: green;
		}
	</style>
</head>
<body>
	<h1> Orlando Flight Catalogue

	<div class="search-box">
		<form method = "GET">
			<input type = "text" name= "search" placeholder= "Search airline or airport..." value="<?php echo $search; ?>">"
		</form>
	</div>

	<table>
		<tr>
			<th>Flight ID</th>
			<th>Airline</th>
			<th>From</th>
			<th>To</th>
			<th>Departure</th>
			<th>Arrival</th>
			<th>Gate</th>
			<th>Terminal </th>
			<th>Aircraft</th>
			<th>Status</th> 
		</tr>

	<?php
	if ($result->num_rows > 0) {
		while ($row=$result->fetch_assoc()) {

			$status = $row['is_delayed'] ?
				"<span class='delayed'>Delayed</span>":
				"<span class='on-time'>On Time </span>";

			echo "<tr>
				<td>
					<a href='flight_details.php?id={$row['flight_id']}'>
						{$row['flight_id']}
					</a>
				</td>
				<td>{$row['airline_name']}</td>
				<td>{$row['departure_code']}</td>
				<td>{$row['arrival_code']}</td>
				<td>{$row['departure_time']}</td>
				<td>{$row['arrival_time']}</td>
				<td>{$row['gate_number']}</td>
				<td>{$row['terminal']}</td>
				<td>{$row['aircraft_type']}</td>
				<td>$status</td>
			</tr>";
		}
	} else {
		echo "<tr><td colspan='10'>No results found</td></tr>";
	}
	?>

	</table>
</body>
</html>
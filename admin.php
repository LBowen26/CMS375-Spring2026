<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
include 'db_connect.php';

/*  Handle Post actions */
$message = "";
$msgType = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* delete flight  */
    if ($action === 'delete_flight') {
        $id = intval($_POST['flight_id']);
        $conn->query("DELETE FROM TicketPrices WHERE flight_id = $id");
        $conn->query("DELETE FROM Flights WHERE flight_id = $id");
        $message = "Flight #$id deleted successfully.";
    }

    /*  insert flight */
    if ($action === 'insert_flight') {
        $fn   = $conn->real_escape_string(trim($_POST['flight_number']));
        $aid  = intval($_POST['airline_id']);
        $dep  = intval($_POST['departure_airport_id']);
        $arr  = intval($_POST['arrival_airport_id']);
        $dt   = $conn->real_escape_string($_POST['departure_time']);
        $at   = $conn->real_escape_string($_POST['arrival_time']);
        $dur  = intval($_POST['flight_duration']);
        $gate = $conn->real_escape_string(trim($_POST['gate_number']));
        $term = $conn->real_escape_string(trim($_POST['terminal']));
        $ac   = $conn->real_escape_string(trim($_POST['aircraft_type']));
        $stat = $conn->real_escape_string($_POST['status']);

        $sql = "INSERT INTO Flights
                    (flight_number, airline_id, departure_airport_id, arrival_airport_id,
                     departure_time, arrival_time, flight_duration, gate_number,
                     terminal, aircraft_type, is_delayed, status, passenger_count, seats_available)
                VALUES
                    ('$fn', $aid, $dep, $arr, '$dt', '$at', $dur,
                     '$gate', '$term', '$ac', 0, '$stat', 0, 0)";
        if ($conn->query($sql)) {
            $message = "Flight '$fn' inserted successfully.";
        } else {
            $message = "Error inserting flight: " . $conn->error;
            $msgType = "error";
        }
    }

    /*  update flight */
    if ($action === 'update_flight') {
        $id   = intval($_POST['flight_id']);
        $fn   = $conn->real_escape_string(trim($_POST['flight_number']));
        $aid  = intval($_POST['airline_id']);
        $dep  = intval($_POST['departure_airport_id']);
        $arr  = intval($_POST['arrival_airport_id']);
        $dt   = $conn->real_escape_string($_POST['departure_time']);
        $at   = $conn->real_escape_string($_POST['arrival_time']);
        $dur  = intval($_POST['flight_duration']);
        $gate = $conn->real_escape_string(trim($_POST['gate_number']));
        $term = $conn->real_escape_string(trim($_POST['terminal']));
        $ac   = $conn->real_escape_string(trim($_POST['aircraft_type']));
        $stat = $conn->real_escape_string($_POST['status']);

        $sql = "UPDATE Flights SET
                    flight_number='$fn', airline_id=$aid,
                    departure_airport_id=$dep, arrival_airport_id=$arr,
                    departure_time='$dt', arrival_time='$at',
                    flight_duration=$dur, gate_number='$gate',
                    terminal='$term', aircraft_type='$ac', status='$stat'
                WHERE flight_id=$id";
        if ($conn->query($sql)) {
            $message = "Flight #$id updated successfully.";
        } else {
            $message = "Error updating flight: " . $conn->error;
            $msgType = "error";
        }
    }

    /*  insert airline  */
    if ($action === 'insert_airline') {
        $name = $conn->real_escape_string(trim($_POST['airline_name']));
        $id   = abs(crc32($name));
        $sql  = "INSERT IGNORE INTO Airlines (airline_id, airline_name) VALUES ($id, '$name')";
        if ($conn->query($sql)) {
            $message = "Airline '$name' added.";
        } else {
            $message = "Error: " . $conn->error;
            $msgType = "error";
        }
    }

    /*  delete airline  */
    if ($action === 'delete_airline') {
        $id = intval($_POST['airline_id']);
        if ($conn->query("DELETE FROM Airlines WHERE airline_id=$id")) {
            $message = "Airline #$id deleted.";
        } else {
            $message = "Cannot delete — airline may still have flights. " . $conn->error;
            $msgType = "error";
        }
    }

    /*  insert airport  */
    if ($action === 'insert_airport') {
        $code = $conn->real_escape_string(strtoupper(trim($_POST['airport_code'])));
        $name = $conn->real_escape_string(trim($_POST['airport_name']));
        $sql  = "INSERT INTO Airports (airport_code, airport_name) VALUES ('$code','$name')";
        if ($conn->query($sql)) {
            $message = "Airport '$code' added.";
        } else {
            $message = "Error: " . $conn->error;
            $msgType = "error";
        }
    }

    /*  delete airport  */
    if ($action === 'delete_airport') {
        $id = intval($_POST['airport_id']);
        if ($conn->query("DELETE FROM Airports WHERE airport_id=$id")) {
            $message = "Airport #$id deleted.";
        } else {
            $message = "Cannot delete — airport may still have flights. " . $conn->error;
            $msgType = "error";
        }
    }

    /*  add / delete ticket price  */
    if ($action === 'add_ticket_price') {
        $fid  = intval($_POST['flight_id']);
        $sec  = $conn->real_escape_string(trim($_POST['section_name']));
        $price= floatval($_POST['ticket_price']);
        $conn->query("INSERT INTO TicketPrices (flight_id, section_name, ticket_price) VALUES ($fid,'$sec',$price)");
        $message = "Ticket price added.";
    }
    if ($action === 'delete_ticket_price') {
        $id = intval($_POST['price_id']);
        $conn->query("DELETE FROM TicketPrices WHERE id=$id");
        $message = "Ticket price removed.";
    }
}

/* Fetch data for display */
$flights  = $conn->query("
    SELECT f.*, a.airline_name,
           dep.airport_code AS dep_code,
           arr.airport_code AS arr_code
    FROM   Flights f
    JOIN   Airlines a   ON f.airline_id           = a.airline_id
    JOIN   Airports dep ON f.departure_airport_id = dep.airport_id
    JOIN   Airports arr ON f.arrival_airport_id   = arr.airport_id
    ORDER  BY f.departure_time DESC
");
$airlines = $conn->query("SELECT * FROM Airlines ORDER BY airline_name");
$airports = $conn->query("SELECT * FROM Airports ORDER BY airport_code");

// Build arrays for dropdowns
$airlineArr = []; $res = $conn->query("SELECT * FROM Airlines ORDER BY airline_name");
while ($r = $res->fetch_assoc()) $airlineArr[] = $r;
$airportArr = []; $res = $conn->query("SELECT * FROM Airports ORDER BY airport_code");
while ($r = $res->fetch_assoc()) $airportArr[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard — Orlando Airport</title>
<style>
* { box-sizing: border-box; }
body { font-family: Arial, sans-serif; background: #f0f3f7; margin: 0; }

/*  Topbar  */
.topbar {
    background: #003a6e;
    color: white;
    padding: 14px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
.topbar h1 { font-size: 1.2em; }
.topbar-right a {
    color: #aad4ff;
    text-decoration: none;
    margin-left: 18px;
    font-size: 0.9em;
}
.topbar-right a:hover { color: white; text-decoration: underline; }

/*  Tabs  */
.tab-bar {
    background: white;
    border-bottom: 2px solid #ddd;
    padding: 0 28px;
    display: flex;
    gap: 4px;
}
.tab-btn {
    padding: 12px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 0.95em;
    color: #555;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.15s;
}
.tab-btn.active, .tab-btn:hover { color: #007BFF; border-bottom-color: #007BFF; }

/*  Content  */
.content { padding: 24px 28px; }

.tab-panel { display: none; }
.tab-panel.active { display: block; }

/*  Message Banner  */
.msg {
    padding: 12px 18px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 0.92em;
}
.msg.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.msg.error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

/*  Cards / panels  */
.panel {
    background: white;
    border-radius: 8px;
    padding: 22px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.08);
}
.panel h2 { font-size: 1.05em; color: #003a6e; margin-bottom: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

/*  Tables  */
table { width: 100%; border-collapse: collapse; font-size: 0.88em; }
th, td { padding: 9px 10px; border: 1px solid #e0e0e0; text-align: left; vertical-align: middle; }
th { background: #007BFF; color: white; white-space: nowrap; }
tr:nth-child(even) { background: #f8f9fc; }
tr:hover { background: #eef4ff; }

/*  Forms  */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    align-items: end;
}
.form-grid label { display: block; font-size: 0.82em; color: #444; font-weight: bold; margin-bottom: 4px; }
.form-grid input, .form-grid select {
    width: 100%; padding: 8px 10px; border: 1px solid #ccc;
    border-radius: 5px; font-size: 0.9em;
}
.form-grid input:focus, .form-grid select:focus { border-color: #007BFF; outline: none; }
.btn {
    padding: 8px 16px; border: none; border-radius: 5px;
    cursor: pointer; font-size: 0.88em; transition: background 0.2s;
}
.btn-primary { background: #007BFF; color: white; }
.btn-primary:hover { background: #005fcc; }
.btn-danger  { background: #dc3545; color: white; }
.btn-danger:hover  { background: #a71d2a; }
.btn-warning { background: #e67e22; color: white; }
.btn-warning:hover { background: #b95e17; }
.btn-sm { padding: 5px 10px; font-size: 0.82em; }

/*  Status badges  */
.badge {
    display: inline-block; padding: 3px 8px; border-radius: 12px;
    font-size: 0.8em; font-weight: bold;
}
.badge-scheduled { background: #d4edda; color: #155724; }
.badge-landed    { background: #e2e3e5; color: #383d41; }
.badge-delayed   { background: #f8d7da; color: #721c24; }
.badge-active    { background: #cce5ff; color: #004085; }

/*  Modal overlay  */
.overlay { display:none; position:fixed; top:0;left:0;width:100%;height:100%;
           background:rgba(0,0,0,0.45); z-index:200; align-items:center; justify-content:center; }
.overlay.open { display:flex; }
.modal { background:white; border-radius:10px; padding:28px 30px; width:700px; max-width:96vw;
         max-height:90vh; overflow-y:auto; box-shadow:0 8px 40px rgba(0,0,0,0.25); }
.modal h2 { font-size:1.1em; color:#003a6e; margin-bottom:18px; }
.modal-close { float:right; background:none; border:none; font-size:1.4em; cursor:pointer; color:#888; }
.modal-close:hover { color:#333; }
</style>
</head>
<body>

<div class="topbar">
    <h1> Orlando Airport — Admin Dashboard</h1>
    <div class="topbar-right">
        <span>Logged in as <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></span>
        <a href="index.php">View Catalogue</a>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" onclick="showTab('flights', this)">Flights</button>
    <button class="tab-btn" onclick="showTab('airlines', this)">Airlines</button>
    <button class="tab-btn" onclick="showTab('airports', this)">Airports</button>
</div>

<div class="content">

<?php if ($message): ?>
    <div class="msg <?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<!-- TAB 1: flights -->
<div id="tab-flights" class="tab-panel active">

    <!-- Insert new flight -->
    <div class="panel">
        <h2> Add New Flight</h2>
        <form method="POST">
            <input type="hidden" name="action" value="insert_flight">
            <div class="form-grid">
                <div>
                    <label>Flight Number</label>
                    <input name="flight_number" placeholder="e.g. AA1234" required>
                </div>
                <div>
                    <label>Airline</label>
                    <select name="airline_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($airlineArr as $al): ?>
                            <option value="<?php echo $al['airline_id']; ?>"><?php echo htmlspecialchars($al['airline_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>From (Airport)</label>
                    <select name="departure_airport_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($airportArr as $ap): ?>
                            <option value="<?php echo $ap['airport_id']; ?>"><?php echo htmlspecialchars($ap['airport_code']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>To (Airport)</label>
                    <select name="arrival_airport_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($airportArr as $ap): ?>
                            <option value="<?php echo $ap['airport_id']; ?>"><?php echo htmlspecialchars($ap['airport_code']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Departure Time</label>
                    <input type="datetime-local" name="departure_time" required>
                </div>
                <div>
                    <label>Arrival Time</label>
                    <input type="datetime-local" name="arrival_time" required>
                </div>
                <div>
                    <label>Duration (min)</label>
                    <input type="number" name="flight_duration" value="0">
                </div>
                <div>
                    <label>Gate</label>
                    <input name="gate_number" placeholder="e.g. B12">
                </div>
                <div>
                    <label>Terminal</label>
                    <input name="terminal" placeholder="e.g. A">
                </div>
                <div>
                    <label>Aircraft Type</label>
                    <input name="aircraft_type" placeholder="e.g. B737">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="scheduled">Scheduled</option>
                        <option value="active">Active</option>
                        <option value="landed">Landed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end;">
                    <button class="btn btn-primary" type="submit">Add Flight</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Flights table -->
    <div class="panel">
        <h2> All Flights</h2>
        <div style="overflow-x:auto;">
        <table>
            <tr>
                <th>ID</th><th>Flight #</th><th>Airline</th>
                <th>From</th><th>To</th>
                <th>Departure</th><th>Arrival</th>
                <th>Gate</th><th>Terminal</th><th>Aircraft</th><th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $flights->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['flight_id']; ?></td>
                <td><?php echo htmlspecialchars($row['flight_number']); ?></td>
                <td><?php echo htmlspecialchars($row['airline_name']); ?></td>
                <td><?php echo htmlspecialchars($row['dep_code']); ?></td>
                <td><?php echo htmlspecialchars($row['arr_code']); ?></td>
                <td><?php echo $row['departure_time']; ?></td>
                <td><?php echo $row['arrival_time']; ?></td>
                <td><?php echo htmlspecialchars($row['gate_number'] ?: '—'); ?></td>
                <td><?php echo htmlspecialchars($row['terminal'] ?: '—'); ?></td>
                <td><?php echo htmlspecialchars($row['aircraft_type'] ?: '—'); ?></td>
                <td>
                    <?php
                    $sc = match($row['status']) {
                        'delayed'   => 'delayed',
                        'active'    => 'active',
                        'landed'    => 'landed',
                        default     => 'scheduled'
                    };
                    ?>
                    <span class="badge badge-<?php echo $sc; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                </td>
                <td style="white-space:nowrap;">
                    <button class="btn btn-warning btn-sm"
                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                    <button class="btn btn-sm" style="background:#17a2b8;color:white;"
                        onclick="openPriceModal(<?php echo $row['flight_id']; ?>,'<?php echo htmlspecialchars($row['flight_number']); ?>')">Prices</button>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Delete flight <?php echo htmlspecialchars($row['flight_number']); ?>?')">
                        <input type="hidden" name="action" value="delete_flight">
                        <input type="hidden" name="flight_id" value="<?php echo $row['flight_id']; ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        </div>
    </div>
</div>

<!-- TAB 2: AIRLINES -->
<div id="tab-airlines" class="tab-panel">
    <div class="panel">
        <h2> Add Airline</h2>
        <form method="POST" style="display:flex;gap:12px;align-items:flex-end;">
            <input type="hidden" name="action" value="insert_airline">
            <div>
                <label style="display:block;font-size:0.85em;font-weight:bold;margin-bottom:4px;">Airline Name</label>
                <input name="airline_name" placeholder="e.g. Delta Airlines" style="padding:8px 12px;border:1px solid #ccc;border-radius:5px;width:280px;" required>
            </div>
            <button class="btn btn-primary" type="submit">Add Airline</button>
        </form>
    </div>
    <div class="panel">
        <h2> All Airlines</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Action</th></tr>
            <?php while ($row = $airlines->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['airline_id']; ?></td>
                <td><?php echo htmlspecialchars($row['airline_name']); ?></td>
                <td>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Delete this airline?')">
                        <input type="hidden" name="action" value="delete_airline">
                        <input type="hidden" name="airline_id" value="<?php echo $row['airline_id']; ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- TAB 3: airports -->
<div id="tab-airports" class="tab-panel">
    <div class="panel">
        <h2> Add Airport</h2>
        <form method="POST" style="display:flex;gap:12px;align-items:flex-end;">
            <input type="hidden" name="action" value="insert_airport">
            <div>
                <label style="display:block;font-size:0.85em;font-weight:bold;margin-bottom:4px;">ICAO Code</label>
                <input name="airport_code" placeholder="e.g. KATL" maxlength="10"
                       style="padding:8px 12px;border:1px solid #ccc;border-radius:5px;width:140px;" required>
            </div>
            <div>
                <label style="display:block;font-size:0.85em;font-weight:bold;margin-bottom:4px;">Airport Name</label>
                <input name="airport_name" placeholder="e.g. Atlanta Hartsfield"
                       style="padding:8px 12px;border:1px solid #ccc;border-radius:5px;width:280px;" required>
            </div>
            <button class="btn btn-primary" type="submit">Add Airport</button>
        </form>
    </div>
    <div class="panel">
        <h2> All Airports</h2>
        <table>
            <tr><th>ID</th><th>Code</th><th>Name</th><th>Action</th></tr>
            <?php while ($row = $airports->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['airport_id']; ?></td>
                <td><?php echo htmlspecialchars($row['airport_code']); ?></td>
                <td><?php echo htmlspecialchars($row['airport_name']); ?></td>
                <td>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Delete this airport?')">
                        <input type="hidden" name="action" value="delete_airport">
                        <input type="hidden" name="airport_id" value="<?php echo $row['airport_id']; ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</div><!-- /.content -->


<!-- Edit Flight -->
<div class="overlay" id="editModal">
<div class="modal">
    <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    <h2> Edit Flight</h2>
    <form method="POST">
        <input type="hidden" name="action" value="update_flight">
        <input type="hidden" name="flight_id" id="edit_flight_id">
        <div class="form-grid">
            <div>
                <label>Flight Number</label>
                <input name="flight_number" id="edit_flight_number" required>
            </div>
            <div>
                <label>Airline</label>
                <select name="airline_id" id="edit_airline_id">
                    <?php foreach ($airlineArr as $al): ?>
                        <option value="<?php echo $al['airline_id']; ?>"><?php echo htmlspecialchars($al['airline_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>From Airport</label>
                <select name="departure_airport_id" id="edit_dep_airport">
                    <?php foreach ($airportArr as $ap): ?>
                        <option value="<?php echo $ap['airport_id']; ?>"><?php echo htmlspecialchars($ap['airport_code']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>To Airport</label>
                <select name="arrival_airport_id" id="edit_arr_airport">
                    <?php foreach ($airportArr as $ap): ?>
                        <option value="<?php echo $ap['airport_id']; ?>"><?php echo htmlspecialchars($ap['airport_code']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Departure Time</label>
                <input type="datetime-local" name="departure_time" id="edit_dep_time">
            </div>
            <div>
                <label>Arrival Time</label>
                <input type="datetime-local" name="arrival_time" id="edit_arr_time">
            </div>
            <div>
                <label>Duration (min)</label>
                <input type="number" name="flight_duration" id="edit_duration">
            </div>
            <div>
                <label>Gate</label>
                <input name="gate_number" id="edit_gate">
            </div>
            <div>
                <label>Terminal</label>
                <input name="terminal" id="edit_terminal">
            </div>
            <div>
                <label>Aircraft Type</label>
                <input name="aircraft_type" id="edit_aircraft">
            </div>
            <div>
                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="scheduled">Scheduled</option>
                    <option value="active">Active</option>
                    <option value="landed">Landed</option>
                    <option value="delayed">Delayed</option>
                </select>
            </div>
        </div>
        <div style="margin-top:18px;display:flex;gap:10px;">
            <button class="btn btn-primary" type="submit">Save Changes</button>
            <button class="btn" type="button" onclick="closeModal('editModal')"
                    style="background:#aaa;color:white;">Cancel</button>
        </div>
    </form>
</div>
</div>

<!-- Ticket Prices -->
<div class="overlay" id="priceModal">
<div class="modal" style="max-width:500px;">
    <button class="modal-close" onclick="closeModal('priceModal')">✕</button>
    <h2> Ticket Prices — <span id="price_flight_label"></span></h2>
    <div id="priceTableWrap">Loading...</div>
    <hr style="margin:16px 0;">
    <h3 style="font-size:0.95em;color:#003a6e;margin-bottom:10px;">Add Price Tier</h3>
    <form method="POST">
        <input type="hidden" name="action" value="add_ticket_price">
        <input type="hidden" name="flight_id" id="price_flight_id">
        <div style="display:flex;gap:10px;align-items:flex-end;">
            <div>
                <label style="display:block;font-size:0.82em;font-weight:bold;margin-bottom:3px;">Class / Section</label>
                <input name="section_name" placeholder="e.g. Economy" style="padding:8px;border:1px solid #ccc;border-radius:5px;">
            </div>
            <div>
                <label style="display:block;font-size:0.82em;font-weight:bold;margin-bottom:3px;">Price ($)</label>
                <input type="number" step="0.01" name="ticket_price" placeholder="199.99"
                       style="padding:8px;border:1px solid #ccc;border-radius:5px;width:110px;">
            </div>
            <button class="btn btn-primary" type="submit">Add</button>
        </div>
    </form>
</div>
</div>

<script>
/*  Tabs */
function showTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

/*  helpers  */
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
window.addEventListener('click', e => {
    if (e.target.classList.contains('overlay')) e.target.classList.remove('open');
});

/*  Edit flight   */
function openEditModal(row) {
    document.getElementById('edit_flight_id').value     = row.flight_id;
    document.getElementById('edit_flight_number').value = row.flight_number;
    document.getElementById('edit_airline_id').value    = row.airline_id;
    document.getElementById('edit_dep_airport').value   = row.departure_airport_id;
    document.getElementById('edit_arr_airport').value   = row.arrival_airport_id;
    document.getElementById('edit_gate').value          = row.gate_number || '';
    document.getElementById('edit_terminal').value      = row.terminal    || '';
    document.getElementById('edit_aircraft').value      = row.aircraft_type || '';
    document.getElementById('edit_duration').value      = row.flight_duration || 0;
    document.getElementById('edit_status').value        = row.status;
    // Convert MySQL datetime to datetime-local format
    const toLocal = s => s ? s.replace(' ', 'T').substring(0,16) : '';
    document.getElementById('edit_dep_time').value = toLocal(row.departure_time);
    document.getElementById('edit_arr_time').value = toLocal(row.arrival_time);
    document.getElementById('editModal').classList.add('open');
}

/* Ticket price modal (using AJAX fetch)  */
function openPriceModal(flightId, flightNum) {
    document.getElementById('price_flight_id').value  = flightId;
    document.getElementById('price_flight_label').textContent = flightNum;
    document.getElementById('priceTableWrap').innerHTML = 'Loading...';
    document.getElementById('priceModal').classList.add('open');

    fetch('admin_prices_ajax.php?flight_id=' + flightId)
        .then(r => r.text())
        .then(html => { document.getElementById('priceTableWrap').innerHTML = html; });
}
</script>
</body>
</html>

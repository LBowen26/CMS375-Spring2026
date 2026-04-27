<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}
include 'db_connect.php';

$fid    = intval($_GET['flight_id'] ?? 0);
$result = $conn->query("SELECT * FROM TicketPrices WHERE flight_id = $fid ORDER BY id");

if ($result->num_rows === 0) {
    echo "<p style='color:#888;font-size:0.9em;'>No ticket prices on record for this flight.</p>";
    exit;
}
?>
<table style="width:100%;border-collapse:collapse;font-size:0.88em;">
<tr>
    <th style="background:#007BFF;color:white;padding:8px;border:1px solid #ddd;">Class</th>
    <th style="background:#007BFF;color:white;padding:8px;border:1px solid #ddd;">Price ($)</th>
    <th style="background:#007BFF;color:white;padding:8px;border:1px solid #ddd;">Remove</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td style="padding:7px 10px;border:1px solid #e0e0e0;"><?php echo htmlspecialchars($row['section_name']); ?></td>
    <td style="padding:7px 10px;border:1px solid #e0e0e0;">$<?php echo number_format($row['ticket_price'], 2); ?></td>
    <td style="padding:7px 10px;border:1px solid #e0e0e0;">
        <form method="POST" action="admin.php" style="display:inline;">
            <input type="hidden" name="action"   value="delete_ticket_price">
            <input type="hidden" name="price_id" value="<?php echo $row['id']; ?>">
            <button type="submit" style="background:#dc3545;color:white;border:none;padding:4px 9px;border-radius:4px;cursor:pointer;font-size:0.82em;">Remove</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

include 'vehicle_db.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

// Handle Update & Delete POST actions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit_booking') {
        $id            = intval($_POST['booking_id']);
        $customer_name = trim($_POST['customer_name']);
        $phone         = trim($_POST['phone']);
        $vehicle_number= trim($_POST['vehicle_number']);
        $vehicle_type  = trim($_POST['vehicle_type']);
        $service_type  = trim($_POST['service_type']);
        $booking_date  = trim($_POST['booking_date']);
        $booking_time  = trim($_POST['booking_time']);

        $prices = [
            "Full Service"       => 12500.00,
            "Body Wash & Vacuum" => 2500.00,
            "Wheel Alignment"    => 3500.00,
            "Engine Tune-up"     => 6000.00
        ];
        $service_fee = $prices[$service_type] ?? 5000.00;

        $stmt = $conn->prepare("UPDATE bookings SET customer_name = ?, phone = ?, vehicle_number = ?, vehicle_type = ?, service_type = ?, service_fee = ?, booking_date = ?, booking_time = ? WHERE id = ?");
        // s s s s s d s s i  → 9 params
        $stmt->bind_param("sssssdssi", $customer_name, $phone, $vehicle_number, $vehicle_type, $service_type, $service_fee, $booking_date, $booking_time, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($_POST['action'] === 'delete_booking') {
        $id = intval($_POST['booking_id']);
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch single record if editing
$edit_booking = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $estmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $estmt->bind_param("i", $edit_id);
    $estmt->execute();
    $edit_booking = $estmt->get_result()->fetch_assoc();
    $estmt->close();

    // Convert HH:MM:SS -> HH:MM for <input type="time">
    if ($edit_booking && !empty($edit_booking['booking_time'])) {
        $edit_booking['booking_time'] = substr($edit_booking['booking_time'], 0, 5);
    }
}

$sql = "SELECT b.*, p.payment_id, p.transaction_ref, p.payment_method
        FROM bookings b
        LEFT JOIN payments p ON b.id = p.booking_id
        ORDER BY b.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - AutoCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>AutoCare Admin Panel</h1>
        <nav>
            <a href="index.html">Home</a>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_dashboard.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Bookings &amp; Payment Records</h2>
        <p style="margin-top: 6px; color:#475569;">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>

        <?php if ($edit_booking): ?>
        <div class="alert-box" style="margin-bottom: 25px; background: #fffbeb; border-color: #fde68a;">
            <h3>Edit Booking #<?php echo $edit_booking['id']; ?></h3>
            <form method="POST" action="admin_dashboard.php" style="margin-top: 15px;">
                <input type="hidden" name="action" value="edit_booking">
                <input type="hidden" name="booking_id" value="<?php echo $edit_booking['id']; ?>">

                <div class="card-row">
                    <div class="form-group">
                        <label>Customer Name:</label>
                        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($edit_booking['customer_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_booking['phone']); ?>" required>
                    </div>
                </div>

                <div class="card-row">
                    <div class="form-group">
                        <label>Vehicle Number:</label>
                        <input type="text" name="vehicle_number" value="<?php echo htmlspecialchars($edit_booking['vehicle_number']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Vehicle Type:</label>
                        <select name="vehicle_type" required>
                            <?php
                            $vtypes = ["Car", "Van", "SUV / Jeep", "Motorbike", "Three-Wheeler"];
                            foreach ($vtypes as $vt) {
                                $sel = ($edit_booking['vehicle_type'] === $vt) ? 'selected' : '';
                                echo "<option value=\"$vt\" $sel>$vt</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Service Type:</label>
                    <select name="service_type" required>
                        <?php
                        $stypes = [
                            "Full Service"       => "Full Service (Rs. 12,500)",
                            "Body Wash & Vacuum" => "Body Wash & Vacuum (Rs. 2,500)",
                            "Wheel Alignment"    => "Wheel Alignment (Rs. 3,500)",
                            "Engine Tune-up"     => "Engine Tune-up (Rs. 6,000)"
                        ];
                        foreach ($stypes as $val => $label) {
                            $sel = ($edit_booking['service_type'] === $val) ? 'selected' : '';
                            echo "<option value=\"$val\" $sel>$label</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="card-row">
                    <div class="form-group">
                        <label>Booking Date:</label>
                        <input type="date" name="booking_date" value="<?php echo htmlspecialchars($edit_booking['booking_date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Booking Time:</label>
                        <input type="time" name="booking_time" value="<?php echo htmlspecialchars($edit_booking['booking_time']); ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Update Booking</button>
                <a href="admin_dashboard.php" class="btn" style="background:#64748b; margin-left:8px;">Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Service</th>
                    <th>Date &amp; Time</th>
                    <th>Fee</th>
                    <th>Payment</th>
                    <th>Txn Ref</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['customer_name']); ?><br>
                                <small><?php echo htmlspecialchars($row['phone']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['vehicle_number']); ?><br>
                                <small><?php echo htmlspecialchars($row['vehicle_type']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['booking_date']); ?><br>
                                <small><?php echo htmlspecialchars(date("h:i A", strtotime($row['booking_time']))); ?></small>
                            </td>
                            <td>Rs. <?php echo number_format($row['service_fee'], 2); ?></td>
                            <td>
                                <?php if (!empty($row['payment_id'])): ?>
                                    <span class="badge-paid">Paid</span><br>
                                    <small><?php echo htmlspecialchars($row['payment_method'] ?? ''); ?></small>
                                <?php else: ?>
                                    <span class="badge-unpaid">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['transaction_ref'] ?? '-'); ?></td>
                            <td>
                                <div class="action-group">
                                    <a href="admin_dashboard.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                        <input type="hidden" name="action" value="delete_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2026 AutoCare Vehicle Service Center.</p>
    </footer>
</body>
</html>
<?php
header('Content-Type: application/json');
include 'vehicle_db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

$name     = trim($_POST['customer_name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$v_number = trim($_POST['vehicle_number'] ?? '');
$v_type   = trim($_POST['vehicle_type'] ?? '');
$s_type   = trim($_POST['service_type'] ?? '');
$b_date   = trim($_POST['booking_date'] ?? '');
$b_time   = trim($_POST['booking_time'] ?? '');

$prices = [
    "Full Service"       => 12500.00,
    "Body Wash & Vacuum" => 2500.00,
    "Wheel Alignment"    => 3500.00,
    "Engine Tune-up"     => 6000.00
];
$fee = $prices[$s_type] ?? 5000.00;

if ($name === '' || $phone === '' || $v_number === '' || $b_date === '' || $b_time === '') {
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
    exit;
}

$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) < 9 || strlen($phoneDigits) > 12) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid phone number."]);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $b_date) || strtotime($b_date) < strtotime(date('Y-m-d'))) {
    echo json_encode(["status" => "error", "message" => "Booking date cannot be in the past."]);
    exit;
}

if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $b_time)) {
    echo json_encode(["status" => "error", "message" => "Invalid booking time."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO bookings (customer_name, phone, vehicle_number, vehicle_type, service_type, service_fee, booking_date, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssdss", $name, $phone, $v_number, $v_type, $s_type, $fee, $b_date, $b_time);

if ($stmt->execute()) {
    echo json_encode([
        "status"     => "success",
        "booking_id" => $stmt->insert_id,
        "amount"     => $fee
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Could not save booking. Please try again."]);
}

$stmt->close();
$conn->close();
?>
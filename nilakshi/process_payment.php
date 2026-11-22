<?php
header('Content-Type: application/json');
include 'vehicle_db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

$booking_id     = intval($_POST['booking_id'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? 'Credit/Debit Card');
$card_number    = preg_replace('/\D/', '', $_POST['card_number'] ?? '');

if ($booking_id <= 0) {
    echo json_encode(["status" => "error", "message" => "A valid Booking ID is required."]);
    exit;
}

$allowed_methods = ["Credit/Debit Card", "Online Bank Transfer", "Cash at Center"];
if (!in_array($payment_method, $allowed_methods, true)) {
    echo json_encode(["status" => "error", "message" => "Invalid payment method."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, service_fee FROM bookings WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Booking not found."]);
    exit;
}

$booking = $res->fetch_assoc();
$stmt->close();

// Check if payment already exists
$chkStmt = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ?");
$chkStmt->bind_param("i", $booking_id);
$chkStmt->execute();
if ($chkStmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Payment has already been completed for this booking."]);
    exit;
}
$chkStmt->close();

$amount   = $booking['service_fee'];
$last4    = strlen($card_number) >= 4 ? substr($card_number, -4) : null;
$txn_ref  = "TXN-" . strtoupper(uniqid());

$payStmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, card_last4, transaction_ref) VALUES (?, ?, ?, ?, ?)");
$payStmt->bind_param("idsss", $booking_id, $amount, $payment_method, $last4, $txn_ref);

if ($payStmt->execute()) {
    echo json_encode([
        "status"          => "success",
        "transaction_ref" => $txn_ref,
        "amount"          => number_format($amount, 2)
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Payment processing failed. Please try again."]);
}

$payStmt->close();
$conn->close();
?>
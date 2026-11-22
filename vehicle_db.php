<?php
mysqli_report(MYSQLI_REPORT_OFF);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "vehicle_db";
$port       = 3306;

$conn = @new mysqli("localhost", 'root', "", "vehicle_db", 3306);

if (!$conn || $conn->connect_errno) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        "status"  => "error",
        "message" => "MySQL connection failed (" . ($conn ? $conn->connect_errno : "0") . "): "
                   . ($conn ? $conn->connect_error : "Unable to initialize mysqli")
                   . ". Check that MySQL is running in XAMPP and database 'vehicle_db' exists."
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
?>
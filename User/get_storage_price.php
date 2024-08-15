<?php
include '../config.php';

if (!isset($_GET['storage_id'])) {
    echo "Error: Storage ID is not set.";
    exit;
}

$storage_id = intval($_GET['storage_id']); // Ensure the ID is an integer

// Fetch the price for the selected storage option
$sql = "SELECT price FROM storage_options WHERE storage_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $storage_id);
$stmt->execute();
$stmt->bind_result($price);
$stmt->fetch();

echo number_format($price, 2);

$stmt->close();
$conn->close();
?>

<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $rawPassword = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($rawPassword)) {
        echo "<script>alert('Please fill in all required fields.'); window.location.href = './index.php';</script>";
        exit;
    }

    $password = password_hash($rawPassword, PASSWORD_BCRYPT);

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'customer')");
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }
    $stmt->bind_param("sss", $username, $password, $email);

    if ($stmt->execute()) {
        // Successful signup. Redirect to index without query string to avoid auto-opening modals.
        // Create a minimal customers row linked to the new user_id so other pages depending on customers work
        $new_user_id = $stmt->insert_id;
        // Create a minimal customers row. last_name set to empty string in SQL.
        $custStmt = $conn->prepare("INSERT INTO customers (user_id, first_name, last_name) VALUES (?, ?, '')");
        if ($custStmt) {
            $custStmt->bind_param('is', $new_user_id, $username);
            $custStmt->execute();
            $custStmt->close();
        }

        echo "<script>alert('Signup successful! Please log in.'); window.location.href = './index.php';</script>";
    } else {
        // If duplicate email / username, show friendly message
        echo "<script>alert('Signup failed: " . addslashes($stmt->error) . "'); window.location.href = './index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

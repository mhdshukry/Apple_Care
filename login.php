<?php
session_start(); // Start the session at the beginning

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Store user_id and other relevant details in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            
            // Redirect based on user role
            if ($row['role'] === 'admin') {
                header("Location: ./admin/admin.php");
                exit();
            } elseif ($row['role'] === 'customer') {
                header("Location: ./user/home.php");
                exit();
            } else {
                echo "Invalid role";
            }
        } else {
            echo "Invalid password";
        }
    } else {
        echo "No user found with that email";
    }

    $stmt->close();
    $conn->close();
}
?>

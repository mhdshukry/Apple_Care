<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, password, email, role) VALUES ('$username', '$password', '$email', 'customer')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Signup successful! Please log in.');
                window.location.href = './index.php?login=true';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

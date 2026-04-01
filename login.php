<?php
// Strengthen session cookie flags early (before session_start)
// Secure flag will apply when served over HTTPS; SameSite=Lax helps against CSRF on top-level nav
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Lax'
    ]);
}
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
        $stored = $row['password'];

        // Handle two cases: stored password already hashed (bcrypt) or legacy plain text
        $is_hashed = (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || strpos($stored, '$2b$') === 0);

        if (($is_hashed && password_verify($password, $stored)) || (!$is_hashed && $password === $stored)) {
            // If legacy plain text password matched, re-hash and update the DB for the user
            if (!$is_hashed) {
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("si", $newHash, $row['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
            }
            // Regenerate session ID to prevent fixation
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            // Store user_id and other relevant details in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // If a redirect target is provided (e.g., from modal), prefer it (but validate to prevent open redirects)
            $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : (isset($_GET['redirect']) ? trim($_GET['redirect']) : '');
            // Allow only relative paths within this app
            if ($redirect) {
                // Disallow full URLs and suspicious schemes
                if (preg_match('#^https?://#i', $redirect) || strpos($redirect, '//') === 0) {
                    $redirect = '';
                }
                // Normalize to strip any CRLF
                $redirect = str_replace(["\r", "\n"], '', $redirect);
                // Basic allowlist: must start with one of our known entry paths
                $allowedPrefixes = [
                    './',
                    '../',
                    'Index.php',
                    'index.php',
                    'products.php',
                    'product_details.php',
                    'User/',
                    'Admin/'
                ];
                $ok = false;
                foreach ($allowedPrefixes as $prefix) {
                    if (stripos($redirect, $prefix) === 0) {
                        $ok = true;
                        break;
                    }
                }
                if (!$ok) {
                    $redirect = '';
                }
            }
            // Redirect based on user role
            if (strtolower($row['role']) === 'admin') {
                header("Location: ./Admin/admin.php");
                exit();
            } elseif (strtolower($row['role']) === 'customer' || strtolower($row['role']) === 'user') {
                if ($redirect && stripos($redirect, 'login.php') === false) {
                    header("Location: " . $redirect);
                } else {
                    header("Location: ./User/products.php");
                }
                exit();
            } else {
                echo "Invalid role";
            }
        } else {
            echo "Invalid email or password";
        }
    } else {
        echo "Invalid email or password";
    }

    $stmt->close();
    $conn->close();
}
?>
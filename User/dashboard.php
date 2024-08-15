<?php
session_start();
$current_page = 'dashboard';

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];  // Get user_id from session

// Include the database configuration file
include '../config.php';

// Handle form submission to update customer data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];

    $sql = "UPDATE customers SET first_name = ?, last_name = ?, phone_number = ?, address = ?, city = ?, state = ?, zip_code = ?, country = ? WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $phone_number, $address, $city, $state, $zip_code, $country, $customer_id);
    $stmt->execute();
    echo "<p class='message success'>Customer data updated successfully!</p>";
}

// Fetch all customers
$sql = "SELECT * FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // Bind user_id as an integer
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Care+</title>
    <link rel="stylesheet" href="../Assets/CSS/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Playwrite+AR:wght@100..400&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: "Nunito", sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-content {
            width: 80%;
            padding: 20px;
        }
        .main-content h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #007bff;
        }
        .customer-card {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h3 {
            margin-top: 0;
            font-size: 18px;
        }
        .card .form-group {
            margin-bottom: 15px;
        }
        .card .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #6c757d;
        }
        .card .form-group input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .card button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .card button:hover {
            background-color: #218838;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="logo">
            <img src="../Assets/Images/apple.png" alt="Logo">
            <span>Apple Care+</span>
        </div>
        <ul>
            <li class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                <a href="./home.php">
                    <i class="fa fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="./category.php">
                    <i class="fa fa-mobile"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="#search-job">
                    <i class="fas fa-cart-plus"></i>
                    <span>Add to Cart</span>
                </a>
            </li>
            <li>
                <a href="#applications">
                    <i class="fa fa-user"></i>
                    <span>About Us</span>
                </a>
            </li>
            <li>
                <a href="#message">
                    <i class="fa fa-info"></i>
                    <span>Contact</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="./dashboard.php">
                    <i class="fa fa-user"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <p>© 2024 by <span>Apple Care</span></p>
            <p>Made with <span style="color: red;">❤</span> by SKR_ATH7</p>
        </div>
        <div class="social-media-footer">
            <ul>
                <li>
                    <a href="#" class="social-media-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-media-link">
                        <i class="fab fa-facebook"></i>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-media-link">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-media-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <h1>Customer Dashboard</h1>

        <?php if (isset($message)): ?>
            <p class="message success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="customer-card">
            <?php foreach ($customers as $customer): ?>
                <div class="card">
                    <form method="POST">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($customer['first_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($customer['last_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($customer['phone_number']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($customer['address']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?= htmlspecialchars($customer['city']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" value="<?= htmlspecialchars($customer['state']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="zip_code">Zip Code</label>
                            <input type="text" id="zip_code" name="zip_code" value="<?= htmlspecialchars($customer['zip_code']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?= htmlspecialchars($customer['country']) ?>">
                        </div>
                        <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer['customer_id']) ?>">
                        <button type="submit">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="../Assets/Js/sidebar.js"></script>
</body>
</html>

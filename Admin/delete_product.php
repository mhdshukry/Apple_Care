<?php
include 'auth.php';
include '../config.php';
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete related cart items first (if any)
        $stmt = $conn->prepare("DELETE FROM add_to_cart WHERE product_id = ?");
        if ($stmt === false) {
            throw new Exception("Error preparing statement for add_to_cart: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for add_to_cart: " . $stmt->error);
        }

        // Delete related records from reviews
        $deleteReviews = "DELETE FROM reviews WHERE product_id = ?";
        $stmt = $conn->prepare($deleteReviews);
        if ($stmt === false) {
            throw new Exception("Error preparing statement for reviews: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for reviews: " . $stmt->error);
        }

        // Delete related records from order_details (if product_id exists there in your schema)
        if ($stmt = $conn->prepare("DELETE FROM order_details WHERE product_id = ?")) {
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement for order_details: " . $stmt->error);
            }
        }

        // Delete related records from product_categories
        if ($stmt = $conn->prepare("DELETE FROM product_categories WHERE product_id = ?")) {
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement for product_categories: " . $stmt->error);
            }
        }

        // Delete variant stock and options referencing this product (avoid FK issues)
        // storage_stock references storage_options.storage_id, so delete storage_stock first, then storage_options
        if ($stmt = $conn->prepare("DELETE ss FROM storage_stock ss JOIN storage_options so ON ss.storage_id = so.storage_id WHERE so.product_id = ?")) {
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Error deleting storage_stock: " . $stmt->error);
            }
        }

        if ($stmt = $conn->prepare("DELETE FROM storage_options WHERE product_id = ?")) {
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Error deleting storage_options: " . $stmt->error);
            }
        }

        // Delete the product
        $deleteProduct = "DELETE FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($deleteProduct);
        if ($stmt === false) {
            throw new Exception("Error preparing statement for products: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing statement for products: " . $stmt->error);
        }

        // Commit the transaction
        if (!$conn->commit()) {
            throw new Exception("Error committing transaction: " . $conn->error);
        }

        // Redirect back to product list with a success message
        header("Location: products_admin.php?deleted=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $conn->rollback();
        // Redirect back with an error message param (urlencoded)
        $msg = urlencode("Delete failed: " . $e->getMessage());
        header("Location: products_admin.php?error=" . $msg);
        exit();
    }
} else {
    echo "Invalid request.";
}
?>
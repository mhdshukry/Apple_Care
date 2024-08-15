<?php
session_start();
include '../config.php';

// Check if the order_detail_id is set in the URL
if (isset($_GET['id'])) {
    $order_detail_id = $_GET['id'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Fetch order details from order_details
        $sql_fetch = "
            SELECT 
                od.order_detail_id,
                od.customer_id,
                od.product_id,
                od.order_date,
                (od.quantity * od.price) AS total_amount,
                od.quantity,
                od.color,
                od.storage_id
            FROM order_details od
            WHERE od.order_detail_id = ?
        ";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param('i', $order_detail_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        $order = $result_fetch->fetch_assoc();
        $stmt_fetch->close();

        if ($order) {
            // Insert order details into orders table
            $sql_insert = "
                INSERT INTO orders (
                    order_id,
                    customer_id,
                    product_id,
                    order_date,
                    total_amount,
                    total_quantity,
                    item_color,
                    storage_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param(
                'iiisdisi',
                $order['order_detail_id'], // Using order_detail_id as order_id
                $order['customer_id'],
                $order['product_id'],
                $order['order_date'],
                $order['total_amount'],
                $order['quantity'],
                $order['color'],
                $order['storage_id']
            );
            $stmt_insert->execute();
            $stmt_insert->close();

            // Update storage_stock table
            $sql_update_stock = "
                UPDATE storage_stock
                SET quantity = quantity - ?
                WHERE product_id = ? AND storage_id = ?
            ";
            $stmt_update_stock = $conn->prepare($sql_update_stock);
            $stmt_update_stock->bind_param('iii', $order['quantity'], $order['product_id'], $order['storage_id']);
            $stmt_update_stock->execute();
            $stmt_update_stock->close();

            // Delete order details from order_details
            $sql_delete = "
                DELETE FROM order_details
                WHERE order_detail_id = ?
            ";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param('i', $order_detail_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Commit transaction
            $conn->commit();

            // Redirect back to manage_order.php
            header("Location: manage_order.php");
            exit();
        } else {
            // If no order details found, rollback and show an error
            $conn->rollback();
            echo "Order details not found.";
        }
    } catch (Exception $e) {
        // If there is an error, rollback the transaction
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>

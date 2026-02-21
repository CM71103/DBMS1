<?php
session_start();
require_once 'config.php';

// Security: Redirect to login if not authenticated
if (!isset($_SESSION['Email'])) {
    header("Location: login1.php");
    exit;
}

$email = $_SESSION['Email'];
$total = 0;

// Fetch User Info
$userQuery = pg_query_params($connection, "SELECT \"UserID\" FROM \"Users\" WHERE \"Email\" = $1", [$email]);
$userData = pg_fetch_assoc($userQuery);

if (!$userData) {
    echo "User session invalid. Please log in again.";
    exit;
}

$userID = $userData['UserID'];

// 1. Process items from session into database if any
if (isset($_SESSION['Cart']) && count($_SESSION['Cart']) > 0) {
    // Get latest cart or create one
    $cartQuery = pg_query_params($connection, "SELECT \"CartID\" FROM \"Cart\" WHERE \"UserID\" = $1 ORDER BY \"CartID\" DESC LIMIT 1", [$userID]);
    $cartData = pg_fetch_assoc($cartQuery);

    if (!$cartData) {
        $newCart = pg_query_params($connection, "INSERT INTO \"Cart\"(\"UserID\") VALUES($1) RETURNING \"CartID\"", [$userID]);
        $cartData = pg_fetch_assoc($newCart);
    }

    $activeCartID = $cartData['CartID'];

    // Insert session items to DB
    foreach ($_SESSION['Cart'] as $item) {
        // Use ON CONFLICT to avoid duplicate items in the same cart
        $upsertQuery = "
            INSERT INTO \"CartItems\" (\"CartID\", \"ItemID\", \"Quantity\")
            VALUES ($1, $2, $3)
            ON CONFLICT (\"CartID\", \"ItemID\")
            DO UPDATE SET \"Quantity\" = \"CartItems\".\"Quantity\" + EXCLUDED.\"Quantity\"
        ";
        pg_query_params($connection, $upsertQuery, [$activeCartID, $item['ItemID'], $item['quant']]);
    }

    // Clear session cart
    $_SESSION['Cart'] = [];
}

// 2. Fetch Active Cart for Display
$cartQuery = pg_query_params($connection, "SELECT \"CartID\" FROM \"Cart\" WHERE \"UserID\" = $1 ORDER BY \"CartID\" DESC LIMIT 1", [$userID]);
$cartData = pg_fetch_assoc($cartQuery);
$activeCartID = $cartData ? $cartData['CartID'] : null;

$cartItemsArray = [];
if ($activeCartID) {
    $itemsQuery = pg_query_params($connection,
        "SELECT ci.\"Quantity\", i.\"Name\", i.\"Price\", i.\"ItemID\"
         FROM \"CartItems\" ci
         JOIN \"Item\" i ON ci.\"ItemID\" = i.\"ItemID\"
         WHERE ci.\"CartID\" = $1",
        [$activeCartID]
    );
    while ($row = pg_fetch_assoc($itemsQuery)) {
        $cartItemsArray[] = $row;
        $total += (float)$row['Price'] * (int)$row['Quantity'];
    }
}

// 3. Handle Checkout
if (isset($_POST['checkout']) && $activeCartID && $total > 0) {
    $orderQuery = pg_query_params($connection, "INSERT INTO \"Order\" (\"UserID\") VALUES ($1) RETURNING \"OrderID\"", [$userID]);
    $orderData = pg_fetch_assoc($orderQuery);

    if ($orderData) {
        $orderID = $orderData['OrderID'];
        $_SESSION['tot'] = $total;

        // Link order and cart
        pg_query_params($connection, 
            "INSERT INTO \"OrderCart\" (\"OrderID\", \"CartID\", \"Amount\") VALUES ($1, $2, $3)", 
            [$orderID, $activeCartID, $total]
        );

        // Success: Redirect to payment
        echo "<script>alert('Checkout successful!'); window.location.href='payment.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Amrita Canteen</title>
    <link rel="stylesheet" href="cart.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; padding: 20px; }
        .cart-box { max-width: 600px; margin: 40px auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .cart-header { background: #ff6f61; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .cart-title { font-size: 20px; font-weight: 600; }
        .cart-items { padding: 20px; min-height: 150px; }
        .cart-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .empty-msg { text-align: center; color: #888; padding: 40px 0; }
        .cart-footer { padding: 20px; background: #fcfcfc; border-top: 1px solid #eee; }
        .summary, .total { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .total { font-weight: bold; font-size: 18px; color: #333; }
        .btn-container { display: flex; gap: 10px; margin-top: 20px; }
        .checkout { flex: 2; background: #00b3a4; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .pay-btn { flex: 1; background: #0070ba; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .checkout:hover { background: #008c7a; }
        .pay-btn:hover { background: #005c99; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #ff6f61; text-decoration: none; }
    </style>
</head>
<body>

<div class="cart-box">
    <div class="cart-header">
        <div class="cart-title">Your Shopping Cart</div>
        <div class="cart-count"><?php echo count($cartItemsArray); ?> Items</div>
    </div>

    <div class="cart-items">
        <?php if (empty($cartItemsArray)): ?>
            <div class="empty-msg">
                <p>Your cart is empty.</p>
                <a href="mainpage1.php" style="color: #ff6f61;">Go to Menu</a>
            </div>
        <?php else: ?>
            <?php foreach ($cartItemsArray as $item): ?>
                <div class="cart-item">
                    <span class="item-name"><?php echo htmlspecialchars($item['Name']); ?></span>
                    <div class="item-details">
                        <span class="item-quantity">x<?php echo $item['Quantity']; ?></span>
                        <span class="item-price" style="margin-left: 15px; font-weight: 500;">₹<?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="cart-footer">
        <div class="summary">
            <span>Subtotal</span>
            <span>₹<?php echo number_format($total, 2); ?></span>
        </div>
        <div class="total">
            <span>Grand Total</span>
            <span>₹<?php echo number_format($total, 2); ?></span>
        </div>
        
        <form method="POST" action="cart.php">
            <div class="btn-container">
                <button type="submit" name="checkout" class="checkout" <?php echo empty($cartItemsArray) ? 'disabled' : ''; ?>>Proceed to Checkout</button>
                <button type="button" class="pay-btn" onclick="location.href='payment.php'">Direct Pay</button>
            </div>
        </form>
    </div>
</div>

<a href="mainpage1.php" class="back-link">← Back to Canteen Menu</a>

</body>
</html>
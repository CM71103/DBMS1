<?php
session_start();
require_once 'config.php';

// Security Check
if (!isset($_SESSION['Email'])) {
    header("Location: login1.php");
    exit;
}

$paymentDone = false;
$orderid = 0;
$totalAmount = 0;
$errorMessage = "";

$email = $_SESSION['Email'];
$userQuery = pg_query_params($connection, "SELECT \"UserID\" FROM \"Users\" WHERE \"Email\" = $1", array($email));
$userData = pg_fetch_assoc($userQuery);

if (!$userData) {
    header("Location: login1.php");
    exit;
}

$userID = $userData['UserID'];

// Check if there is an active order to pay for
$orderQuery = pg_query_params($connection, "SELECT \"OrderID\" FROM \"Order\" WHERE \"UserID\" = $1 ORDER BY \"OrderID\" DESC LIMIT 1", array($userID));
$orderData = pg_fetch_assoc($orderQuery);

if (!$orderData) {
    $errorMessage = "No active order found. Please visit your cart and checkout first.";
} else {
    $currentOrderID = $orderData['OrderID'];
    
    // Fetch total amount for this order
    $amountQuery = pg_query_params($connection, "SELECT \"Amount\" FROM \"OrderCart\" WHERE \"OrderID\" = $1", array($currentOrderID));
    $amountData = pg_fetch_assoc($amountQuery);
    $totalAmount = $amountData ? $amountData['Amount'] : 0;
}

if (isset($_POST['PAY']) && !$errorMessage) {
    $cardNo = trim($_POST['cardno']);
    $cvv = trim($_POST['cvv']);
    $cardHold = trim($_POST['cardhold']);

    // 1. Insert into Payment table
    $insertPayment = pg_query_params(
        $connection,
        "INSERT INTO \"Payment\" (\"UserID\", \"CardNumber\", \"cvv\", \"CName\") VALUES ($1, $2, $3, $4) RETURNING \"PaymentID\"",
        array($userID, $cardNo, $cvv, $cardHold)
    );
    
    $paymentData = pg_fetch_assoc($insertPayment);

    if ($paymentData) {
        $paymentID = $paymentData['PaymentID'];

        // 2. Fetch active cart ID
        $cartQuery = pg_query_params($connection, "SELECT \"CartID\" FROM \"Cart\" WHERE \"UserID\"=$1 ORDER BY \"CartID\" DESC LIMIT 1", array($userID));
        $cartData = pg_fetch_assoc($cartQuery);
        $activeCartID = $cartData ? $cartData['CartID'] : null;

        if ($activeCartID) {
            // 3. Link Payment with Order
            $linkQuery = pg_query_params(
                $connection,
                "INSERT INTO \"Payment_order\" (\"PaymentID\", \"CartID\", \"OrderID\", \"Amount\") VALUES ($1, $2, $3, $4)",
                array($paymentID, $activeCartID, $currentOrderID, $totalAmount)
            );

            if ($linkQuery) {
                $paymentDone = true;
                $orderid = $currentOrderID;
                // Success - we can clear certain session flags if needed
            } else {
                $errorMessage = "Payment was recorded but failed to link to the order. Please contact support.";
            }
        } else {
            $errorMessage = "Transaction aborted: No active cart found.";
        }
    } else {
        $errorMessage = "Payment processing failed. Please check your card details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment - Amrita Canteen</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      color: #fff;
    }
    .container {
      width: 100%;
      max-width: 480px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(20px);
      padding: 40px;
      border-radius: 24px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      text-align: center;
      border: 1px solid rgba(255,255,255,0.1);
    }
    h2 { font-weight: 600; margin-bottom: 30px; }
    .status-msg { background: rgba(255, 87, 87, 0.2); border: 1px solid #ff5757; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; }
    label { display: block; text-align: left; margin-bottom: 8px; font-size: 13px; font-weight: 300; opacity: 0.9; }
    input {
      width: 100%;
      padding: 14px;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 12px;
      margin-bottom: 20px;
      background: rgba(255,255,255,0.1);
      color: #fff;
      font-size: 15px;
      box-sizing: border-box;
    }
    input::placeholder { color: rgba(255,255,255,0.4); }
    input:focus { outline: none; border-color: #fff; background: rgba(255,255,255,0.2); }
    .payment-btn {
      width: 100%;
      padding: 16px;
      background: #43e97b;
      color: #1a1a1a;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }
    .payment-btn:hover { background: #38f9d7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(67, 233, 123, 0.4); }
    .payment-btn:disabled { background: #888; cursor: not-allowed; transform: none; box-shadow: none; }

    .success-box {
      background: rgba(67, 233, 123, 0.15);
      border: 1px solid #43e97b;
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 20px;
    }
    .success-box h3 { color: #43e97b; font-size: 24px; margin-top: 0; }
    .success-box p { font-size: 16px; margin: 10px 0; opacity: 0.9; }
    
    .btn-secondary {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 30px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.3s;
    }
    .btn-secondary:hover { background: rgba(255, 255, 255, 0.3); }

    .amount-display { font-size: 18px; margin-bottom: 20px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 10px; }
  </style>
</head>
<body>

<div class="container">
<?php if ($paymentDone): ?>
  <div class="success-box">
    <h3>✅ Payment Success!</h3>
    <p>Order <strong>#<?php echo $orderid; ?></strong> is confirmed.</p>
    <p><strong>Paid:</strong> ₹<?php echo number_format($totalAmount, 2); ?></p>
  </div>
  <a href="mainpage1.php" class="btn-secondary">Return to Menu</a>

<?php else: ?>
  <h2>Complete Payment</h2>
  
  <?php if ($errorMessage): ?>
    <div class="status-msg"><?php echo $errorMessage; ?></div>
    <a href="cart.php" class="btn-secondary">Back to Cart</a>
  <?php else: ?>
    <div class="amount-display">Amount to Pay: <strong>₹<?php echo number_format($totalAmount, 2); ?></strong></div>
    
    <form method="POST" action="payment.php">
        <label>Cardholder Name</label>
        <input type="text" name="cardhold" placeholder="e.g. Rahul Sharma" required>

        <label>Card Number</label>
        <input type="text" name="cardno" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>

        <div style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <label>Expiry Date</label>
                <input type="text" id="expiry" placeholder="MM/YY" maxlength="5" required>
            </div>
            <div style="flex: 1;">
                <label>CVV</label>
                <input type="password" name="cvv" maxlength="3" placeholder="123" required>
            </div>
        </div>

        <button type="submit" name="PAY" class="payment-btn">Pay ₹<?php echo number_format($totalAmount, 2); ?></button>
    </form>
  <?php endif; ?>
<?php endif; ?>
</div>

<script>
  const expiry = document.getElementById('expiry');
  if (expiry) {
    expiry.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      e.target.value = value;
    });
  }
</script>
</body>
</html>

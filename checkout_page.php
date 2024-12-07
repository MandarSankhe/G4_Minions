<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

include('dbinit.php');
include('Cart.php');

// Retrieve errors from the session if available
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
} else {
    $errors = [];
}

function displayError($field) {
    global $errors;
    return isset($errors[$field]) ? '⚠️ ' . $errors[$field] : '';
}

// Initialize Cart class
$cart = new Cart($dbc, $_SESSION['userid']);

// Fetch cart items and totals
$cartItems = $cart->getCartItems();
$subtotal = $cart->getTotalPrice();
$taxRate = 0.10;
$taxAmount = $subtotal * $taxRate;
$finalTotal = $subtotal + $taxAmount;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/CSS/style.css">
    <style>
        .checkout-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .checkout-form,
        .cart-summary {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .checkout-form {
            flex: 2;
        }

        .cart-summary {
            flex: 1;
            color: #007bff;
        }

        .cart-summary h4 {
            margin-bottom: 20px;
            font-weight: bold;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .cart-summary .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .cart-summary .total {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 15px;
        }

        .cart-summary .discount-code {
            margin: 15px 0;
            display: flex;
            gap: 10px;
        }

        .cart-summary .discount-code input {
            flex: 1;
        }

        .cart-summary {
            width: 100%;
            padding: 10px;
            color: #007bff;
            background-color: white;
            border: none;
            border-radius: 5px;
            text-transform: uppercase;
            font-weight: bold;
            cursor: pointer;
        }
        .error-message {
            color: red
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container nav-custom-container">
            <a class="navbar-brand" href="#">
                <img src="./public/images/logo.png" class="logo" />
                Minions TVstore
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-items">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart_page.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="checkout-container">
            <!-- Left Section: Checkout Form -->
            <div class="checkout-form">
                <h2>Checkout</h2>
                <form action="process_checkout.php" method="POST">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName">
                        <span class="error-message"><?= displayError('firstName') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName">
                        <span class="error-message"><?= displayError('lastName') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="text" class="form-control" id="email" name="email">
                        <span class="error-message"><?= displayError('email') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                        <span class="error-message"><?= displayError('phone') ?></span>
                    </div>
                    <h4>Shipping Information</h4>
                    <div class="form-group">
                        <label for="streetAddress">Street Address</label>
                        <input type="text" class="form-control" id="streetAddress" name="street_address" placeholder="123 Main St">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="Enter city">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="state">State</label>
                            <input type="text" class="form-control" id="state" name="state" placeholder="Enter state">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="postalCode">Postal Code</label>
                            <input type="text" class="form-control" id="postalCode" name="postal_code" placeholder="Enter postal code">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country" placeholder="Enter country">
                        </div>
                    </div>
                    <span class="error-message"><?= displayError('address') ?></span>

                    <h4>Payment</h4>
                        <div class="form-group">
                            <label for="cardNumber">Card Number</label>
                            <input type="text" class="form-control" id="cardNumber" name="cardNumber">
                            <span class="error-message"><?= displayError('cardNumber') ?></span>
                        </div>
                        <div class="form-group">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="text" class="form-control" id="expiryDate" name="expiryDate">
                            <span class="error-message"><?= displayError('expiryDate') ?></span>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv">
                            <span class="error-message"><?= displayError('cvv') ?></span>
                        </div>
                    <button type="submit" class="btn btn-primary w-100">Pay Now</button>
                </form>
            </div>

            <!-- Right Section: Cart Summary -->
            <div class="cart-summary">
                <h4>Review Your Cart</h4>
                <?php if (empty($cartItems)): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="item">
                            <span><?= htmlspecialchars($item['Brand']) ?> (x<?= $item['quantity'] ?>)</span>
                            <span>$<?= number_format($item['Price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="item">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="item">
                        <span>Tax (10%):</span>
                        <span>$<?= number_format($taxAmount, 2) ?></span>
                    </div>
                    <div class="total">
                        <span>Total:</span>
                        <span>$<?= number_format($finalTotal, 2) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

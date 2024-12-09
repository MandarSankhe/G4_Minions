<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

include('dbinit.php');
include('Cart.php');

$userid = null;
if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
}

// Retrieve errors and inputs from the session if available
$errors = $_SESSION['errors'] ?? [];
$inputs = $_SESSION['inputs'] ?? [];
unset($_SESSION['errors'], $_SESSION['inputs']);

function displayError($field) {
    global $errors;
    return isset($errors[$field]) ? '<span class="error-message">⚠️ ' . $errors[$field] . '</span>' : '';
}

function stickyValue($field, $default = '') {
    global $inputs;
    return htmlspecialchars($inputs[$field] ?? $default);
}

// Initialize Cart class
$cart = new Cart($dbc, $_SESSION['userid']);

// Fetch cart items and totals
$cartItems = $cart->getCartItems();
$subtotal = $cart->getTotalPrice();
$taxRate = 0.10;
$taxAmount = $subtotal * $taxRate;
$finalTotal = $subtotal + $taxAmount;


// Calculate the cart count
$cart = new Cart($dbc, $userid);
$cartCount = $cart-> getCartCountFromCookie();
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
        .checkout-container { display: flex; flex-wrap: wrap; gap: 15px; }
        .checkout-form, .cart-summary { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .checkout-form { flex: 2; }
        .cart-summary { flex: 1; color: #007bff; }
        .cart-summary h4 { margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .cart-summary .item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .cart-summary .total { font-weight: bold; font-size: 1.2em; margin-top: 15px; }
        .error-message { color: red; font-size: 0.9rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container nav-custom-container">
            <a class="navbar-brand" href="index.php">
                <img src="./public/images/logo.png" class="logo" />
                Minions TVstore
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-items">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart_page.php">
                            Cart 
                            <?php if ($cartCount > 0) : ?>
                                <span class="badge badge-danger"><?= $cartCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php">Order History</a>
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
                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?= stickyValue('firstName') ?>">
                        <?= displayError('firstName') ?>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?= stickyValue('lastName') ?>">
                        <?= displayError('lastName') ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="text" class="form-control" id="email" name="email" value="<?= stickyValue('email') ?>">
                        <?= displayError('email') ?>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= stickyValue('phone') ?>">
                        <?= displayError('phone') ?>
                    </div>
                    <h4>Shipping Information</h4>
                    <div class="form-group">
                        <label for="streetAddress">Street Address</label>
                        <input type="text" class="form-control" id="streetAddress" name="street_address" value="<?= stickyValue('streetAddress') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= stickyValue('city') ?>">
                        
                        </div>
                        <div class="form-group col-md-6">
                            <label for="state">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?= stickyValue('state') ?>">
                           
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="postalCode">Postal Code</label>
                            <input type="text" class="form-control" id="postalCode" name="postal_code" value="<?= stickyValue('postalCode') ?>">
                           
                            <?= displayError('postalCode') ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?= stickyValue('country') ?>">
                         
                        </div>
                        <?= displayError('address') ?>
                    </div>
                   
                    <h4>Payment</h4>
                    <div class="form-group">
                        <label for="cardNumber">Card Number</label>
                        <input type="text" class="form-control" id="cardNumber" name="cardNumber" value="<?= stickyValue('cardNumber') ?>">
                        <?= displayError('cardNumber') ?>
                    </div>
                    <div class="form-group">
                        <label for="expiryDate">Expiry Date</label>
                        <input type="text" class="form-control" id="expiryDate" name="expiryDate" placeholder="YYYY-MM" value="<?= stickyValue('expiryDate') ?>">
                        <?= displayError('expiryDate') ?>
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" class="form-control" id="cvv" name="cvv" value="<?= stickyValue('cvv') ?>">
                        <?= displayError('cvv') ?>
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
                    <div class="item"><span>Subtotal:</span><span>$<?= number_format($subtotal, 2) ?></span></div>
                    <div class="item"><span>Tax (10%):</span><span>$<?= number_format($taxAmount, 2) ?></span></div>
                    <div class="total"><span>Total:</span><span>$<?= number_format($finalTotal, 2) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

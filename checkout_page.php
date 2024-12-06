<?php
session_start(); 

// Fetch user ID from session
if (isset($_SESSION['userid'])) {
    $userId = $_SESSION['userid'];
} else {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Include necessary files
include('dbinit.php');
include('Cart.php');

// Initialize the Cart class
$cart = new Cart($dbc, $userId);

// Fetch cart items and total price
$cartItems = $cart->getCartItems();
$totalPrice = $cart->getTotalPrice();

// Calculate additional information for summary
$subtotal = $totalPrice;
$taxRate = 0.10; // 10% tax
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
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container nav-custom-container">
            <a class="navbar-brand" href="#">
                <img src="./public/images/logo.png" class="logo" />
                Minions TVstore
            </a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Checkout</h2>
        <form action="thank_you.php" method="POST" id="checkoutForm">
            <!-- User Details -->
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" class="form-control" id="fullName" name="fullName" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>

            <!-- Shipping Address -->
            <h4>Shipping Address</h4>
            <div class="form-group">
                <label for="shippingAddress">Address</label>
                <input type="text" class="form-control" id="shippingAddress" name="shippingAddress" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="shippingCity">City</label>
                    <input type="text" class="form-control" id="shippingCity" name="shippingCity" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="shippingZip">Postal Code</label>
                    <input type="text" class="form-control" id="shippingZip" name="shippingZip" required>
                </div>
            </div>

            <!-- Billing Address -->
            <h4>Billing Address</h4>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="sameAsShipping" onclick="copyShippingToBilling()">
                <label class="form-check-label" for="sameAsShipping">Same as Shipping Address</label>
            </div>
            <div class="form-group">
                <label for="billingAddress">Address</label>
                <input type="text" class="form-control" id="billingAddress" name="billingAddress" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="billingCity">City</label>
                    <input type="text" class="form-control" id="billingCity" name="billingCity" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="billingZip">Postal Code</label>
                    <input type="text" class="form-control" id="billingZip" name="billingZip" required>
                </div>
            </div>

            <!-- Payment Method -->
            <h4>Payment</h4>
            <div class="form-group">
                <label for="paymentMethod">Payment Method</label>
                <select class="form-control" id="paymentMethod" name="paymentMethod" onchange="toggleCardDetails()" required>
                    <option value="">Select Payment Method</option>
                    <option value="card">Card</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <div id="cardDetails" style="display: none;">
                <div class="form-group">
                    <label for="cardNumber">Card Number</label>
                    <input type="text" class="form-control" id="cardNumber" name="cardNumber" pattern="\d{16}" title="Enter a valid 16-digit card number">
                </div>
                <div class="form-group">
                    <label for="expiryDate">Expiry Date</label>
                    <input type="month" class="form-control" id="expiryDate" name="expiryDate">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" class="form-control" id="cvv" name="cvv" pattern="\d{3}" title="Enter a valid 3-digit CVV">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Submit</button>
        </form>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; 2024 | TVstore</p>
        </div>
    </footer>

    <script>
        // Toggle card details based on payment method
        function toggleCardDetails() {
            const paymentMethod = document.getElementById('paymentMethod').value;
            const cardDetails = document.getElementById('cardDetails');
            if (paymentMethod === 'card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        }

        // Copy shipping address to billing address
        function copyShippingToBilling() {
    const sameAsShipping = document.getElementById('sameAsShipping').checked;
    const billingAddressFields = [
        document.getElementById('billingAddress'),
        document.getElementById('billingCity'),
        document.getElementById('billingZip')
    ];

    if (sameAsShipping) {
        // Copy values from shipping to billing and disable fields
        document.getElementById('billingAddress').value = document.getElementById('shippingAddress').value;
        document.getElementById('billingCity').value = document.getElementById('shippingCity').value;
        document.getElementById('billingZip').value = document.getElementById('shippingZip').value;
        billingAddressFields.forEach(field => field.setAttribute('disabled', 'true'));
    } else {
        // Clear and enable billing fields
        billingAddressFields.forEach(field => {
            field.removeAttribute('disabled');
            field.value = '';
        });
    }
}

    </script>
</body>

</html>

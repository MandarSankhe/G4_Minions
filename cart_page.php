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

// Handle actions: updating quantity or removing items
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['update_quantity'])) {
        $tvId = $_POST['tvId'];
        $quantity = (int)$_POST['quantity'];

        // Increment or decrement the quantity
        if ($_POST['update_quantity'] == 'increment') {
            $quantity++; // Increase quantity by 1
        } elseif ($_POST['update_quantity'] == 'decrement') {
            $quantity--; // Decrease quantity by 1
        }
        // Remove item from cart if quantity is 0
        if ($quantity == 0) {
            $cart->removeFromCart($tvId);
        }
        else {
            $cart->updateQuantity($tvId, $quantity);
        }
    }

    if (isset($_POST['remove_item'])) {
        $tvId = $_POST['tvId'];
        $cart->removeFromCart($tvId);
    }
}

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
    <title>Cart</title>
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
        <div class="row">
            <!-- Cart items -->
            <div class="col-md-7">
                <h2 class="cart-header">Your Cart</h2>

                <?php if (empty($cartItems)): ?>
                    <div class="alert alert-info">Your cart is empty.</div>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-card">
                            <?php
                                // Fetch image URL, if null -> display default image
                                $imageURL = empty($item['ImageURL']) ? "./public/images/tv/default.png" : "./public/images/tv/" . htmlspecialchars($item['ImageURL']);
                            ?>
                            <img src="<?= $imageURL ?>" alt="TV Image">
                            <div class="cart-card-details">
                                <h5><?= htmlspecialchars($item['Brand']) ?> - <?= htmlspecialchars($item['Model']) ?></h5>
                                <p><?= number_format($item['Price'], 2) ?> CAD</p>
                                <form action="" method="POST" class="d-inline">
                                    <input type="hidden" name="tvId" value="<?= $item['ID'] ?>">
                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] ?>">
                                    <!-- Quantity Actions -->
                                    <div class="quantity-actions">
                                        <div>
                                            <button type="submit" name="update_quantity" value="decrement" class="btn btn-primary">-</button>
                                            <span><?= $item['quantity'] ?></span>
                                            <button type="submit" name="update_quantity" value="increment" class="btn btn-primary">+</button>
                                        </div>
                                    </div>
                                </form>
                                
                            </div>
                            <div class="cart-card-actions">
                                <form action="" method="POST" class="d-inline mt-2">
                                    <input type="hidden" name="tvId" value="<?= $item['ID'] ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Right side: Cart summary -->
            <div class="col-md-5">
                <!-- Cart Summary Section -->
                <div class="cart-summary">
                    <h3>Summary</h3>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <span><?= htmlspecialchars($item['Brand']) ?> - <?= htmlspecialchars($item['Model']) ?> (x<?= $item['quantity'] ?>)</span>
                            <span class="summary-price"><?= number_format($item['Price'] * $item['quantity'], 2) ?> CAD</span>
                        </div>
                    <?php endforeach; ?>
                    <hr/>
                    <div class="summary-item">
                        <span>Subtotal:</span> 
                        <span class="summary-price"><?= number_format($subtotal, 2) ?> CAD</span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (10%):</span> 
                        <span class="summary-price"><?= number_format($taxAmount, 2) ?> CAD</span>
                    </div>
                    <hr/>
                    <div class="summary-item cart-total">
                        <span>Total Price:</span> 
                        <span class="summary-price total-amount"><?= number_format($finalTotal, 2) ?> CAD</span>
                    </div>
                    <a href="checkout_page.php" class="btn btn-success btn-block mt-3">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p>&copy; 2024 | TVstore</p>
            <p>Website developed by: Minions</p>
            <p>Course: PHP Programming with MySQL</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

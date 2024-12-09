<?php
session_start();

// Include necessary files
include('dbinit.php');
include('Cart.php');

$userId = null;
$usertype = '';

if (isset($_SESSION['userid'])) {
    // Fetch user ID from session
    $userId = $_SESSION['userid'];
    // Get the user type from the session
    $usertype = $_SESSION['usertype'];
}

// Check if the user is an admin
$isAdmin = $usertype == 'admin';



// Initialize the Cart class
$cart = new Cart($dbc, $userId);

// Fetch product ID from GET parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$productId = (int)$_GET['id'];

// Fetch product details
$query = "SELECT * FROM Products WHERE ID = ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger'>Product not found!</div>";
    exit();
}

// Initialize quantity
$quantity = $_POST['quantity'] ?? 1;

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        if ($_POST['update_quantity'] === 'increment') {
            $quantity++;
        } elseif ($_POST['update_quantity'] === 'decrement' && $quantity > 1) {
            $quantity--;
        }
    } elseif (isset($_POST['add_to_cart'])) {
        $cart->addToCart($productId, $quantity);
        header("Location: cart_page.php");
        exit();
    }
}

// Calculate the cart count
$cartCount = $cart-> getCartCountFromCookie();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['Model']) ?> - Product Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/CSS/style.css">
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
                    <!-- Do not display cart nav link for admin -->
                    <?php if(!$isAdmin) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart_page.php">
                                Cart 
                                <?php if ($cartCount > 0) : ?>
                                    <span class="badge badge-danger"><?= $cartCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Display Order history if user is logged in and of type customer (not admin) -->
                    <?php if(!empty($userId) && !$isAdmin) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="order_history.php">Order History</a>
                        </li>
                    <?php endif; ?>
                    <!-- Display login link if user is not logged in -->
                    <?php if(empty($userId)) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>

                    <!-- Display logout link if user is logged in -->
                    <?php if(!empty($userId)) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6">
                <img src="<?= empty($product['ImageURL']) ? "./public/images/tv/default.png" : htmlspecialchars($product['ImageURL']) ?>" alt="<?= htmlspecialchars($product['Model']) ?>" class="img-fluid">
            </div>

            <!-- Product Details -->
            <div class="col-md-6">
                <h2><?= htmlspecialchars($product['Model']) ?> - <?= htmlspecialchars($product['Brand']) ?></h2>
                <p><?= htmlspecialchars($product['Description']) ?></p>
                <h4>$<?= number_format($product['Price'], 2) ?></h4>
                <p>Availability: <?= $product['Stock'] === 'instock' ? 'In Stock' : 'Pre-Order' ?></p>

                <?php if(!$isAdmin): ?> <!-- Do not display quantity to add to cart for admin (admin will only view product details) -->
                    <!-- Quantity and Add to Cart Form -->
                    <form action="" method="POST" class="mt-4">
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <div class="quantity-actions d-inline-flex bg-light align-items-center">
                                <!-- Decrement Button -->
                                <button type="submit" name="update_quantity" value="decrement" class="btn btn-primary">-</button>
                                <input type="hidden" name="quantity" value="<?= htmlspecialchars($quantity) ?>">
                                <span class="px-3"><?= htmlspecialchars($quantity) ?></span>
                                <!-- Increment Button -->
                                <button type="submit" name="update_quantity" value="increment" class="btn btn-primary">+</button>
                            </div>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-success mt-3">Add to Cart</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
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

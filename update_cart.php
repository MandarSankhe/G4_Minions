<?php
// Start the session
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$userID = $_SESSION['userid'];

// Include the database connection and Cart class files
include('dbinit.php');
include('cart.php');

// Create an instance of the Cart class
$cart = new Cart($dbc, $userID);

// Check if the TV ID and quantity are passed via GET or POST
if (isset($_GET['id'])) {
    $tvID = $_GET['id'];
    $quantity = 1; // Default to 1 when added to cart from home page

    // Add the TV item to the cart
    $cart->addToCart($tvID, $quantity);

    // Redirect to the cart page
    header("Location: cart_page.php");
    exit();
} else {
    // Redirect to the homepage if no TV ID is provided
    header("Location: index.php");
    exit();
}
?>

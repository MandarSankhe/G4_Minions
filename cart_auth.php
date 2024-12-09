<!-- Cart auth requires unauthenticated users to register/login before proceeding to checkout page. 
 After login, also saves the session cart details in database -->
<?php

// Start the session
session_start();

// Include necessary files
include('dbinit.php');
include('Cart.php');

// Check if the user is logged in
if (!isset($_SESSION['userid'])) {
    // User is not logged in
    $_SESSION['redirect_to_cart_auth_after_login'] = true; // Set flag in session to redirect to cart_auth.php after login
    header("Location: login.php"); // Redirect to login page
    exit();
} else {
    // User is logged in
    if (isset($_SESSION['redirect_to_cart_auth_after_login']) && $_SESSION['redirect_to_cart_auth_after_login'] === true) { // Flag is set
        // Handle transfer of session data to the database
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $cart = new Cart($dbc, $_SESSION['userid']);  // Initialize with the database connection and user ID
            $cart->insertSessionCartToDatabase();  // Transfer the session cart data to the database
            
            // Clear the session cart data
            unset($_SESSION['cart']);
        }
    
        // Clear the flag
        unset($_SESSION['redirect_to_cart_auth_after_login']);
    }

    // Redirect to the checkout page
    header("Location: checkout_page.php");
    exit();
}

?>
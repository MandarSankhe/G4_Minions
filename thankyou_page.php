<?php
    // Start the session
    session_start();
    $userid = null;
    if (isset($_SESSION['userid'])) {
        $userid = $_SESSION['userid'];
    }

    $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : ''; // Retrieve from URL

    include('dbinit.php');
    include('cart.php');

    // Calculate the cart count
    $cart = new Cart($dbc, $userid);
    $cartCount = $cart-> getCartCountFromCookie();
?>
          
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Order</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="public/CSS/style.css" rel="stylesheet">
    <style>
        .thank-you-container {
            max-width: 700px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .thank-you-container h1 {
            font-size: 2.5rem;
            color: #28a745;
        }
        .thank-you-container img {
            width: 200px;
            margin: 20px auto;
        }
        .thank-you-container p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .btn-print {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s ease-in-out;
        }
        .btn-print:hover {
            background-color: #218838;
        }
        .footer-note {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 20px;
        }
        .additional-info {
            font-size: 1rem;
            margin-top: 20px;
            color: #343a40;
        }
        .highlight {
            font-weight: bold;
            color: #28a745;
        }
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
    <div class="thank-you-container">
        <img src="public/Images/thankyou.jpg" alt="Thank You Image">
        <h1>Thank You!</h1>
        <p>Your order has been placed successfully. We appreciate your trust in us.</p>

        <form method="post" action="generate_invoice.php">
           <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8'); ?>">
             <button type="submit" class="btn btn-primary">Generate Invoice</button>
        </form>
        <div class="footer-note">
           <p>Thank you for shopping with Minions TVstore!</p>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

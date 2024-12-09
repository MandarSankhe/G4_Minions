<?php
// Start the session
session_start();

$username = '';
$usertype = '';
$userid = null;

if (isset($_SESSION['username'])) {
    // Get the username and user type from the session
    $username = $_SESSION['username'];
    $usertype = $_SESSION['usertype'];
    $userid = $_SESSION['userid'];
}

// Check if the user is an admin
$isAdmin = $usertype == 'admin';

// Including the file that initializes db connection and TV class.
include('dbinit.php');
include('cart.php');

// Create an instance of the Television class
$tv = new Television($dbc);

// Filter list variables
$searchQuery = $_GET['searchQuery'] ?? '';
$stockFilter = $_GET['stockFilter'] ?? '';
$sortOrder = $_GET['sortOrder'] ?? '';

// Fetch TVs from db
$tvList = $tv->getAllTVs($searchQuery, $stockFilter, $sortOrder);

// Check if there are any TVs in result set
$hasRecords = count($tvList) > 0;


// Calculate the cart count
$cart = new Cart($dbc, $userid);
$cartCount = $cart-> getCartCountFromCookie();


// Closing the database connection.
$dbc->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TVstore</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/CSS/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container nav-custom-container">
            <a class="navbar-brand" href="index.php">
                <img src="./public/images/logo.png" alt="logo" class="logo" />
                Minions TVstore
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavr" aria-controls="navbarNavr" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavr">
                <ul class="navbar-nav ms-auto nav-items">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>

                    <?php if($isAdmin) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="insert_data.php">Add New TV</a>
                        </li>
                    <?php endif; ?>

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
                    <?php if(!empty($username) && !$isAdmin) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="order_history.php">Order History</a>
                        </li>
                    <?php endif; ?>
                    <!-- Display login link if user is not logged in -->
                    <?php if(empty($username)) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>

                    <!-- Display logout link if user is logged in -->
                    <?php if(!empty($username)) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="jumbotron jumbotron-fluid text-white bannerimg">
        <div class="container text-center">
            <h1 class="display-3">
                Minions TV Store
            </h1>
            <h4 class="header-username">Welcome, <?php echo htmlspecialchars(!empty($username) ? $username : 'Guest'); ?>!</h4>
            <!-- Conditional content based on usertype -->
            <?php if ($isAdmin): ?>
                <a href="insert_data.php" class="btn btn-success btn-lg mt-3">Add New TV</a>
            <?php else: ?>
                <p class="lead">Find the best collection of TVs here.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container mb-5">

        <!-- Filter container -->
        <div class="container mb-4">
            <form id="filter-products-form" method="GET" action="index.php" class="form-inline justify-content-end">
                <!-- Search Field -->
                <div class="form-group mx-2 filter-field">
                    <label for="searchQuery">Search</label>
                    <input type="text" name="searchQuery" class="form-control" placeholder="Type to search..." value="<?= htmlspecialchars($searchQuery) ?>">
                </div>

                <!-- Stock Filter Dropdown -->
                <div class="form-group mx-2 filter-field">
                    <label for="stockFilter">Stock</label>
                    <select name="stockFilter" class="form-control">
                        <option value="" <?= empty($_GET['stockFilter']) ? 'selected' : '' ?>>None</option> <!-- None selected by default -->
                        <option value="instock" <?= ($_GET['stockFilter'] ?? '') === 'instock' ? 'selected' : '' ?>>In Stock</option>
                        <option value="preorder" <?= ($_GET['stockFilter'] ?? '') === 'preorder' ? 'selected' : '' ?>>Pre-Order</option>
                    </select>
                </div>

                <!-- Sort By Dropdown -->
                <div class="form-group mx-2 filter-field">
                    <label for="sortOrder">Sort by</label>
                    <select name="sortOrder" class="form-control">
                        <option value="" <?= empty($_GET['sortOrder']) ? 'selected' : '' ?>>None</option>
                        <option value="price_asc" <?= ($_GET['sortOrder'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= ($_GET['sortOrder'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary mx-2">Filter</button>

                <!-- Clear Filter Button -->
                <a href="index.php" class="btn btn-secondary mx-2">Clear Filter</a>

            </form>
        </div>

        
        <?php if ($hasRecords): ?>
            <!-- TVs list -->
            <div class="row tv-container">
                <?php
                foreach ($tvList as $row) {
                    // Fetch image URL, if null -> display default image
                    $imageURL = empty($row['ImageURL']) ? "./public/images/tv/default.png" : htmlspecialchars($row['ImageURL']);
                    ?>
                    
                    <div class="col-md-4 mb-4">
                        <a href="product_details.php?id=<?= $row['ID'] ?>" class="text-decoration-none text-dark">
                            <div class="card h-100">
                                <!-- TV image -->
                                <img src="<?= $imageURL ?>" class="card-img-top" alt="TV Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['Model']) ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($row['Brand']) ?></h6>
                                    <p class="card-text description-column-products"><?= htmlspecialchars($row['Description']) ?></p>
                                    <p class="card-text">
                                        <?= $row['Stock'] === 'instock' ? 'In Stock' : 'Pre-Order' ?><br>
                                        <strong>$<?= number_format($row['Price'], 2) ?></strong>
                                    </p>
                                </div>
                                <div class="card-footer text-center">
                                    <!-- Conditionally display buttons for admin and customers -->
                                    <?php if ($isAdmin): ?>
                                        <!-- Show "Update" and "Delete" buttons for admin -->
                                        <a href="update_data.php?id=<?= $row['ID'] ?>" class="btn btn-primary btn-sm card-tv-actions">Update</a>
                                        <a href="delete_data.php?id=<?= $row['ID'] ?>" class="btn btn-danger btn-sm card-tv-actions">Delete</a>
                                    <?php else: ?>
                                        <!-- Show "Add to Cart" button for customers -->
                                        <a href="product_details.php?id=<?= $row['ID'] ?>" class="btn btn-primary btn-sm card-tv-actions">View Details</a>
                                        <a href="update_cart.php?id=<?= $row['ID'] ?>" class="btn btn-primary btn-sm card-tv-actions">Add to Cart</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                <?php } ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                No records found.
            </div>
        <?php endif; ?>
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
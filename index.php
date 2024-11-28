<?php
// Start the session
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION['username'];

// Including the file that initializes the database connection.
include('dbinit.php');

// Query to fetch all the data from the books table from the database.
$query = "SELECT * FROM Products";
$result = mysqli_query($dbc, $query);

// Checking if there are any rows in the result set.
$hasRecords = mysqli_num_rows($result) > 0;

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
            <a class="navbar-brand" href="#">Minions TVstore</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-items">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="jumbotron jumbotron-fluid text-white bannerimg">
        <div class="container text-center">
            <h1 class="display-3">
                Minions TV Store
            </h1>
            <h4 class="header-username">Welcome, <?php echo htmlspecialchars($username); ?>!</h4>
            <p class="lead">Find the best collection of TVs here.</p>
            <a href="insert_data.php" class="btn btn-success btn-lg mt-3">Add New TV</a>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($hasRecords): ?>
            <!-- TVs list -->
            <div class="row tv-container">
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    // Fetch image URL, if null -> display default image
                    $imageURL = empty($row['ImageURL']) ? "./public/Images/tv/default.png" : htmlspecialchars($row['ImageURL']);
                    ?>
                    
                    <div class="col-md-4 mb-4">
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
                                <a href="update_data.php?id=<?= $row['ID'] ?>" class="btn btn-primary btn-sm">Update</a>
                                <a href="delete_data.php?id=<?= $row['ID'] ?>" class="btn btn-danger btn-sm">Delete</a>
                            </div>
                        </div>
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
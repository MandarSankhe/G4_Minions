<?php
session_start(); 

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || ($_SESSION['usertype'] ?? null) !== 'admin') {
    // Redirect to login if not logged in or not an admin
    header("Location: login.php");
    exit();
}

// This below line is including the file that initializes the database connection.
include('dbinit.php');
include('television.php');
include('imgur_api_handler.php'); // Include the Imgur API handler

// Retrieve the TV ID from the query string (GET request). If not provided, $id will be null.
$id = $_GET['id'] ?? null;

// These below line will initialize flags for tracking success and errors.
$success = false;
$error = '';

// If an ID is provided, a TV is selected for deletion.
if ($id) 
{
    // Instantiate Television class
    $tv = new Television($dbc);
    $tv->setId($id);  // Set the ID of the TV to be deleted.

    // Retrieve the TV details to get the image URL before deletion
    $tvDetails = $tv->getTvById($id);
    $imageUrl = $tvDetails['image'] ?? null;

    // This below if block checks if the form has been submitted via POST request or not.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        // Call delete method of TV object
        $deleteResult = $tv->deleteTv();

        if($deleteResult === true) {
            // If TV record deletion is successful, proceed to delete the image from Imgur
            if (!empty($imageUrl)) {
                $imageId = basename($imageUrl); // Extract the image ID from the URL
                $imgurDeleteResult = ImgurApiHandler::deleteImage($imageId);

                if (!$imgurDeleteResult) {
                    $error = "Warning: Could not delete the associated image from Imgur.";
                }
            }

            $success = true;
            header("Location: index.php");
            exit();
        } else {
            $error = $deleteResult;
        }
    }
}

// This below line will close the DataBase connection.
$dbc->close();
?>

<!-- HTML code starts here.  -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete TV</title>
    <!-- Link to Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/CSS/style.css">
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container nav-custom-container">
            <a class="navbar-brand" href="index.php">
                <img src="./public/images/logo.png" class="logo" />
                Minions TVstore
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-items">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header text-center bg-danger text-white">
                        <h2>Delete TV</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center">TV deleted successfully!</div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger text-center"><?= $error ?></div>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <strong>Warning:</strong> Are you sure you want to delete this TV?
                            </div>

                            <!-- The below form starts here, with the POST method. Action is empty as this is a self-processing page -->
                            <form method="POST" action="" class="text-center">
                                <button type="submit" class="btn btn-danger btn-lg">Yes, Delete</button>
                                <a href="index.php" class="btn btn-secondary btn-lg ml-2">Cancel</a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

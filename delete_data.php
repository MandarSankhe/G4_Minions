<?php
// This below line is including the file that initializes the database connection.
include('dbinit.php');

// Retrieve the TV ID from the query string (GET request). If not provided, $id will be null.
$id = $_GET['id'] ?? null;

// These below line will initialize flags for tracking success and errors.
$success = false;
$error = '';

// If an ID is provided, a TV is selected for deletion.
if ($id) 
{
    // This below if block checks if the form has been submitted via POST request or not.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        // This below code prepares a statement to delete the TV with the given ID.
        $stmt = mysqli_prepare($dbc, "DELETE FROM Products WHERE ID = ?");

        // Binding the TV ID (integer) to the prepared statement.
        mysqli_stmt_bind_param($stmt, 'i', $id);

        // This below if block will execute the statement and check if the data is deleted or not.
        if (mysqli_stmt_execute($stmt)) 
        {
            // If it is successfully deleted then it redirects the user to index.php
            $success = true;
            header("Location: index.php");
            exit();
        } 
        else 
        {
            // It will come to this else block in case of any error and it will store it into "$error" variable.
            $error = 'Error: ' . mysqli_error($dbc);
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
</head>

<body class="bg-light">
    <!-- Navbar -->
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

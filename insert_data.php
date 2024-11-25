<?php

// Include the file that initializes the database connection.
include('dbinit.php');

// Initializing variables for form values and error messages.
$bookName = '';
$author = '';
$bookDescription = '';
$quantity = '';
$price = '';
$success = false;
$error = '';

// Array to store field-specific error messages.
$fieldErrors = [
    'bookName' => '',
    'bookDescription' => '',
    'author' => '',
    'quantity' => '',
    'price' => ''
];

// Check if the form is submitted via POST method.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collecting and sanitizing form inputs.
    $bookName = mysqli_real_escape_string($dbc, trim($_POST['BookName']));
    $author = mysqli_real_escape_string($dbc, trim($_POST['Author']));
    $bookDescription = mysqli_real_escape_string($dbc, trim($_POST['BookDescription']));
    $quantity = mysqli_real_escape_string($dbc, trim($_POST['QuantityAvailable']));
    $price = mysqli_real_escape_string($dbc, trim($_POST['Price']));

    // Validating inputs and handling errors.
    if (empty($bookName)) {
        $fieldErrors['bookName'] = "Book Name is required.";
    }
    if (empty($author)) {
        $fieldErrors['author'] = "Author Name is required.";
    }
    if (empty($bookDescription)) {
        $fieldErrors['bookDescription'] = "Description is required.";
    }
    if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
        $fieldErrors['quantity'] = "Quantity is required and must be a positive number.";
    }
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $fieldErrors['price'] = "Price is required and must be a positive number.";
    }

    // If there are no errors, proceed to insert data.
    if (array_filter($fieldErrors) == []) {
        // Prepare an SQL statement to insert the form data into the database.
        $stmt = mysqli_prepare($dbc, "INSERT INTO books (BookName, Author, BookDescription, QuantityAvailable, Price, ProductAddedBy) VALUES (?, ?, ?, ?, ?, 'Mandar')");
        mysqli_stmt_bind_param($stmt, 'sssds', $bookName, $author, $bookDescription, $quantity, $price);

        // Execute and check if the statement was successful.
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            header("Location: index.php");
            exit();
        } else {
            // Display SQL errors, if any.
            $error = 'Error: ' . mysqli_error($dbc);
        }
    } else {
        $error = "Please fix the following errors:";
    }
}

// Closing the database connection.
$dbc->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book</title>
    <!-- Import Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Import custom CSS -->
    <link rel="stylesheet" href="public/CSS/style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Mandar BookStore</a>
        </div>
    </nav>

    <!-- Container for the form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Card to wrap the form -->
                <div class="card shadow-lg">
                    <div class="card-header text-white bannerimg text-center">
                        <h2 class="mb-0">Add New Book</h2>
                    </div>
                    <div class="card-body">
                        <!-- Display success or error message -->
                        <?php if ($success): ?>
                            <div class="alert alert-success">Book added successfully!</div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <!-- Form starts here -->
                        <form name="bookForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="form-row">
                                <!-- Book Name Field -->
                                <div class="form-group col-md-6">
                                    <label for="BookName">Book Name<span class="text-danger">*</span></label>
                                    <input type="text" name="BookName" class="form-control" id="BookName" value="<?= htmlspecialchars($bookName) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['bookName']) ?></small>
                                </div>
                                <!-- Author Name Field -->
                                <div class="form-group col-md-6">
                                    <label for="Author">Author Name<span class="text-danger">*</span></label>
                                    <input type="text" name="Author" class="form-control" id="Author" value="<?= htmlspecialchars($author) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['author']) ?></small>
                                </div>
                            </div>
                            <div class="form-row">
                                <!-- Book Description Field -->
                                <div class="form-group col-md-6">
                                    <label for="BookDescription">Description<span class="text-danger">*</span></label>
                                    <textarea name="BookDescription" class="form-control" id="BookDescription" rows="5"><?= htmlspecialchars($bookDescription) ?></textarea>
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['bookDescription']) ?></small>
                                </div>
                                <div class="form-group col-md-6">
                                    <!-- Quantity Field -->

                                    <label for="QuantityAvailable">Quantity Available<span class="text-danger">*</span></label>
                                    <input type="number" name="QuantityAvailable" class="form-control" id="QuantityAvailable" value="<?= htmlspecialchars($quantity) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['quantity']) ?></small>
                                    <br>
                                    <!-- Price Field -->

                                    <label for="Price">Price<span class="text-danger">*</span></label>
                                    <input type="number" name="Price" class="form-control" id="Price" step="0.01" value="<?= htmlspecialchars($price) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['price']) ?></small>

                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-success">Add Book</button>
                                <a href="index.php" class="btn btn-secondary">Back to Home</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
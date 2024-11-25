<?php
// This below line is including the file that initializes the database connection.
include('dbinit.php');

// Initializing Key Variables.
$id = $_GET['id'] ?? null;
$book = null;
$success = false;
$error = '';

// Array to store field-specific error messages.
$fieldErrors = [
    'name' => '',
    'author' => '',
    'description' => '',
    'quantity' => '',
    'price' => ''
];

if ($id) {
    // Fetching the book by ID.
    $query = "SELECT * FROM books WHERE BookID = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $book = mysqli_fetch_assoc($result);

    // Handle the case where no book is found.
    if (!$book) {
        $error = "Book not found.";
    }
}

// Initializing form values and setting default values based on the book details.
$name = htmlspecialchars($book['BookName'] ?? '', ENT_QUOTES, 'UTF-8');
$author = htmlspecialchars($book['Author'] ?? '', ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($book['BookDescription'] ?? '', ENT_QUOTES, 'UTF-8');
$quantity = htmlspecialchars($book['QuantityAvailable'] ?? '', ENT_QUOTES, 'UTF-8');
$price = htmlspecialchars($book['Price'] ?? '', ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get the form input values.
    $name = htmlspecialchars(trim($_POST['BookName']), ENT_QUOTES, 'UTF-8');
    $author = htmlspecialchars(trim($_POST['Author']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['BookDescription']), ENT_QUOTES, 'UTF-8');
    $quantity = htmlspecialchars(trim($_POST['QuantityAvailable']), ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars(trim($_POST['Price']), ENT_QUOTES, 'UTF-8');

    // Validate inputs.
    if (empty($name)) {
        $fieldErrors['name'] = "Book Name is required.";
    }
    if (empty($author)) {
        $fieldErrors['author'] = "Author Name is required.";
    }
    if (empty($description)) {
        $fieldErrors['description'] = "Description is required.";
    }
    if (empty($quantity) || !is_numeric($quantity) || intval($quantity) <= 0) {
        $fieldErrors['quantity'] = "Quantity is required and must be a positive number.";
    }
    if (empty($price) || !is_numeric($price) || floatval($price) <= 0) {
        $fieldErrors['price'] = "Price is required and must be a positive number.";
    }

    // If there are no validation errors, update the book details.
    if (array_filter($fieldErrors) == []) {
        $stmt = mysqli_prepare($dbc, "UPDATE books SET BookName = ?, BookDescription = ?, Author = ?, QuantityAvailable = ?, Price = ? WHERE BookID = ?");
        mysqli_stmt_bind_param($stmt, 'sssidi', $name, $author, $description, $quantity, $price, $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            header("Location: index.php");
            exit();
        } else {
            $error = 'Error updating book: ' . mysqli_error($dbc);
        }
    } else {
        $error = "Please fix the following errors.";
    }
}

// Close the database connection.
$dbc->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Book</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/CSS/style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Mandar BookStore</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Using Bootstrap card component for a cleaner look -->
                <div class="card shadow-lg">
                    <div class="card-header text-white bannerimg text-center">
                        <h2 class="mb-0">Update Book</h2>
                    </div>
                    <div class="card-body">
                        <!-- Display success or error message -->
                        <?php if ($success): ?>
                            <div class="alert alert-success">Book updated successfully!</div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <!-- Only show form if book exists -->
                        <?php if ($book): ?>
                            <form name="bookForm" method="POST" action="">
                                <div class="form-row">
                                    <!-- Book Name Field -->
                                    <div class="form-group col-md-6">
                                        <label>Book Name<span class="required-asterisk">*</span></label>
                                        <input type="text" name="BookName" class="form-control" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <!-- Author Name Field -->
                                    <div class="form-group col-md-6">
                                        <label>Author Name<span class="required-asterisk">*</span></label>
                                        <input type="text" name="Author" class="form-control" value="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['author'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <!-- Book Description Field -->
                                        <label>Description<span class="required-asterisk">*</span></label>
                                        <textarea name="BookDescription" class="form-control" rows="5"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['description'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>

                                    <!-- Quantity Available Field -->
                                    <div class="form-group col-md-6">
                                        <label>Quantity Available<span class="required-asterisk">*</span></label>
                                        <input type="number" name="QuantityAvailable" class="form-control" value="<?= htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['quantity'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <br>

                                        <!-- Price Field -->

                                        <label>Price<span class="required-asterisk">*</span></label>
                                        <input type="number" step="0.01" name="Price" class="form-control" value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['price'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <!-- Submit and Back Buttons -->
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Update Book</button>
                                    <a href="index.php" class="btn btn-secondary">Back to Home</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
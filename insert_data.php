<?php

// Include the file that initializes the database connection.
include('dbinit.php');

// Initializing variables for form values and error messages.
$tvModel = '';
$tvBrand = '';
$tvDescription = '';
$tvStock = '';
$price = '';
$success = false;
$error = '';

// Array to store field-specific error messages.
$fieldErrors = [
    'tvModel' => '',
    'tvBrand' => '',
    'tvDescription' => '',
    'tvStock' => '',
    'price' => ''
];

// Check if the form is submitted via POST method.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collecting and sanitizing form inputs.
    $tvModel = mysqli_real_escape_string($dbc, trim($_POST['tvModel']));
    $tvBrand = mysqli_real_escape_string($dbc, trim($_POST['tvBrand']));
    $tvDescription = mysqli_real_escape_string($dbc, trim($_POST['tvDescription']));
    $tvStock = mysqli_real_escape_string($dbc, trim($_POST['tvStock']));
    $price = mysqli_real_escape_string($dbc, trim($_POST['Price']));

    // Validating inputs and handling errors.
    if (empty($tvModel)) {
        $fieldErrors['tvModel'] = "Model is required.";
    }
    if (empty($tvBrand)) {
        $fieldErrors['tvBrand'] = "Brand is required.";
    }
    if (empty($tvDescription)) {
        $fieldErrors['tvDescription'] = "Description is required.";
    }
    if (empty($tvStock)) {
        $fieldErrors['tvStock'] = "Stock is required.";
    }
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $fieldErrors['price'] = "Price is required and must be a positive number.";
    }

    // If there are no errors, proceed to insert data.
    if (array_filter($fieldErrors) == []) {
        // Prepare an SQL statement to insert the form data into the database.
        $stmt = mysqli_prepare($dbc, "INSERT INTO Products (Model, Brand, Description, Stock, Price) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssd', $tvModel, $tvBrand, $tvDescription, $tvStock, $price);

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
    <title>Add New TV</title>
    <!-- Import Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Import custom CSS -->
    <link rel="stylesheet" href="public/CSS/style.css">
</head>

<body>

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

    <!-- Container for the form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Card to wrap the form -->
                <div class="card shadow-lg">
                    <div class="card-header text-white bannerimg text-center">
                        <h2 class="mb-0">Add New TV</h2>
                    </div>
                    <div class="card-body">
                        <!-- Display success or error message -->
                        <?php if ($success): ?>
                            <div class="alert alert-success">TV added successfully!</div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <!-- Form starts here -->
                        <form name="newTVForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="form-row">
                                <!-- TV Model Field -->
                                <div class="form-group col-md-6">
                                    <label for="tvModel">TV Model<span class="text-danger">*</span></label>
                                    <input type="text" name="tvModel" class="form-control" id="tvModel" value="<?= htmlspecialchars($tvModel) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['tvModel']) ?></small>
                                </div>
                                <!-- TV Brand Field -->
                                <div class="form-group col-md-6">
                                    <label for="tvBrand">Brand<span class="text-danger">*</span></label>
                                    <input type="text" name="tvBrand" class="form-control" id="tvBrand" value="<?= htmlspecialchars($tvBrand) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['tvBrand']) ?></small>
                                </div>
                            </div>
                            <div class="form-row">
                                <!-- TV Description Field -->
                                <div class="form-group col-md-6">
                                    <label for="tvDescription">Description<span class="text-danger">*</span></label>
                                    <textarea name="tvDescription" class="form-control" id="tvDescription" rows="5"><?= htmlspecialchars($tvDescription) ?></textarea>
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['tvDescription']) ?></small>
                                </div>
                                <div class="form-group col-md-6">
                                    <!-- Stock Field -->
                                    <label for="tvStock">Stock<span class="text-danger">*</span></label>
                                    <select name="tvStock" class="form-control" id="tvStock">
                                        <option value="instock" <?= ($tvStock == 'instock') ? 'selected' : '' ?>>In Stock</option>
                                        <option value="preorder" <?= ($tvStock == 'preorder') ? 'selected' : '' ?>>Pre-order</option>
                                    </select>
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['tvStock']) ?></small>
                                    <br>
                                    <!-- Price Field -->
                                    <label for="Price">Price<span class="text-danger">*</span></label>
                                    <input type="number" name="Price" class="form-control" id="Price" step="0.01" value="<?= htmlspecialchars($price) ?>">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['price']) ?></small>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-success">Add TV</button>
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
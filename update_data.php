<?php
// Including the file that initializes the database connection.
include('dbinit.php');
include('Television.php');  // Include the Television class

// Initializing Key Variables.
$id = $_GET['id'] ?? null;
$tv = null;
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

if ($id) {
    // Fetching TV details
    $tvObj = new Television($dbc); // Create new Television object
    $tv = $tvObj->getTvById($id); // Fetch the TV by ID

    // No TV is found.
    if (!$tv) {
        $error = "TV not found.";
    }
}

// Initializing form values and setting default values based on the tv details.
$tvModel = htmlspecialchars($tv['Model'] ?? '', ENT_QUOTES, 'UTF-8');
$tvBrand = htmlspecialchars($tv['Brand'] ?? '', ENT_QUOTES, 'UTF-8');
$tvDescription = htmlspecialchars($tv['Description'] ?? '', ENT_QUOTES, 'UTF-8');
$tvStock = htmlspecialchars($tv['Stock'] ?? '', ENT_QUOTES, 'UTF-8');
$price = htmlspecialchars($tv['Price'] ?? '', ENT_QUOTES, 'UTF-8');
$tvImage = htmlspecialchars($tv['ImageURL'] ?? '', ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get the form input values.
    $tvModel = htmlspecialchars(trim($_POST['tvModel']), ENT_QUOTES, 'UTF-8');
    $tvBrand = htmlspecialchars(trim($_POST['tvBrand']), ENT_QUOTES, 'UTF-8');
    $tvDescription = htmlspecialchars(trim($_POST['tvDescription']), ENT_QUOTES, 'UTF-8');
    $tvStock = htmlspecialchars(trim($_POST['tvStock']), ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars(trim($_POST['price']), ENT_QUOTES, 'UTF-8');
    $tvImage = htmlspecialchars(trim($_POST['tvImage']), ENT_QUOTES, 'UTF-8');

    // Validate input
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
    if (empty($price) || !is_numeric($price) || floatval($price) <= 0) {
        $fieldErrors['price'] = "Price is required and must be a positive number.";
    }

    // If there are no validation errors, update the tv details.
    if (array_filter($fieldErrors) == []) {

        // Update properties of the TV object
        $tvObj->setId($id);
        $tvObj->setModel($tvModel);
        $tvObj->setBrand($tvBrand);
        $tvObj->setDescription($tvDescription);
        $tvObj->setStock($tvStock);
        $tvObj->setPrice($price);
        $tvObj->setImageUrl($tvImage);

        // Call the update method of the TV object
        $updateResult = $tvObj->updateTv();

        if($updateResult === true) {
            $success = true;
            header("Location: index.php");
            exit();
        } else {
            $error = $updateResult;
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
    <title>Update TV</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Using Bootstrap card component for a cleaner look -->
                <div class="card shadow-lg">
                    <div class="card-header text-white bannerimg text-center">
                        <h2 class="mb-0">Update TV</h2>
                    </div>
                    <div class="card-body">
                        <!-- Display success or error message -->
                        <?php if ($success): ?>
                            <div class="alert alert-success">TV updated successfully!</div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <!-- Only show form if TV exists -->
                        <?php if ($tv): ?>
                            <form name="tvUpdateForm" method="POST" action="">
                                <div class="form-row">
                                    <!-- TV Model Field -->
                                    <div class="form-group col-md-6">
                                        <label for="tvModel">TV Model<span class="required-asterisk">*</span></label>
                                        <input type="text" name="tvModel" class="form-control" value="<?= htmlspecialchars($tvModel, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['tvModel'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <!-- TV Brand Field -->
                                    <div class="form-group col-md-6">
                                        <label for="tvBrand">TV Brand<span class="required-asterisk">*</span></label>
                                        <input type="text" name="tvBrand" class="form-control" value="<?= htmlspecialchars($tvBrand, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['tvBrand'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <!-- TV Description Field -->
                                        <label for="tvDescription">Description<span class="required-asterisk">*</span></label>
                                        <textarea name="tvDescription" class="form-control" rows="5"><?= htmlspecialchars($tvDescription, ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['tvDescription'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <!-- Stock Field -->
                                        <label for="tvStock">Stock<span class="required-asterisk">*</span></label>
                                        <select name="tvStock" class="form-control" id="tvStock">
                                            <option value="instock" <?= ($tvStock == 'instock') ? 'selected' : '' ?>>In Stock</option>
                                            <option value="preorder" <?= ($tvStock == 'preorder') ? 'selected' : '' ?>>Pre-order</option>
                                        </select>
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['tvStock'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <br>

                                        <!-- Price Field -->
                                        <label for="price">Price<span class="required-asterisk">*</span></label>
                                        <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="text-danger"><?= htmlspecialchars($fieldErrors['price'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <!-- TV Image Field -->
                                    <div class="form-group col-md-6">
                                        <label for="tvImage">Image</label>
                                        <input type="text" name="tvImage" class="form-control" id="tvImage" value="<?= htmlspecialchars($tvImage) ?>">
                                    </div>
                                    <!-- Submit Button -->
                                    <div class="form-group col-md-6">
                                        <!-- <button type="submit" class="btn btn-success">Add TV</button>
                                        <a href="index.php" class="btn btn-secondary">Back to Home</a> TODO -->
                                    </div>
                                </div>
                                <!-- Submit and Back Buttons -->
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Update TV</button>
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
<?php
session_start(); 

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || ($_SESSION['usertype'] ?? null) !== 'admin') {
    // Redirect to login if not logged in or not an admin
    header("Location: login.php");
    exit();
}

// Include the file that initializes the database connection.
include('dbinit.php');
include('television.php'); // Include the TV class
include('imgur_api_handler.php'); // Include script for Imgur Image API handler

// Initializing variables for form values and error messages.
$tvModel = '';
$tvBrand = '';
$tvDescription = '';
$tvStock = '';
$price = '';
$tvImage = '';
$success = false;
$error = '';
$imgUploadError = '';

// Array to store field-specific error messages.
$fieldErrors = [
    'tvModel' => '',
    'tvBrand' => '',
    'tvDescription' => '',
    'tvStock' => '',
    'price' => '',
    'tvImage' => ''
];

// Check if the form is submitted via POST method.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collecting and sanitizing form inputs.
    $tvModel = mysqli_real_escape_string($dbc, trim($_POST['tvModel']));
    $tvBrand = mysqli_real_escape_string($dbc, trim($_POST['tvBrand']));
    $tvDescription = mysqli_real_escape_string($dbc, trim($_POST['tvDescription']));
    $tvStock = mysqli_real_escape_string($dbc, trim($_POST['tvStock']));
    $price = mysqli_real_escape_string($dbc, trim($_POST['Price']));
    $tvImage = ''; // Default value

    // Validating inputs and handling errors
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
    if (isset($_FILES['tvImage']) && $_FILES['tvImage']['error'] == UPLOAD_ERR_OK) {
        $imgName = $_FILES['tvImage']['name'];
        $fileType = pathinfo($imgName, PATHINFO_EXTENSION); // Get file type

        // Validate file upload formats if image is provided
        $allowTypes = array('jpg', 'png', 'jpeg');
        if(!in_array($fileType, $allowTypes)) {
            $fieldErrors['tvImage'] = "Only image with extensions .jpg, .png or .jpeg allowed.";
        }
    }

    // If there are no errors, proceed to insert data.
    if (array_filter($fieldErrors) == []) {
        
        // Handle image upload with IMGUR REST API
        if (isset($_FILES['tvImage']) && $_FILES['tvImage']['error'] == UPLOAD_ERR_OK) {

            // Call postImageImgur static method from imgur_api_handler
            $uploadResult = ImgurApiHandler::postImageImgur($_FILES['tvImage']['tmp_name']);

            if ($uploadResult['success']) {
                $tvImage = $uploadResult['url']; // Get uploaded image URL
            } else {
                $imgUploadError = "Image upload failed: " . $uploadResult['error'];
            }
        }

        // Proceed with insert if image upload is successful
        if(empty($imgUploadError)) {

            // Create an instance of the Television class
            $tv = new Television($dbc);
    
            // Set the values for the TV object
            $tv->setModel($tvModel);
            $tv->setBrand($tvBrand);
            $tv->setDescription($tvDescription);
            $tv->setStock($tvStock);
            $tv->setPrice($price);
            $tv->setImageUrl($tvImage);
    
            // Call the insertTv method to insert the TV into the database
            $insertResult = $tv->insertTv();
    
            if ($insertResult === true) {
                $success = true;
                header("Location: index.php");
                exit();
            } else {
                // Display SQL errors
                $error = $insertResult;
            }
        }

    } else {
        $error = "Please fix the following errors:";
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
            <a class="navbar-brand" href="index.php">
                <img src="./public/images/logo.png" alt="logo" class="logo" />
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
                        <a class="nav-link" href="insert_data.php">Add New TV</a>
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
                        <form name="newTVForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
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
                            <div class="form-row align-items-end">
                                <!-- TV Image Field -->
                                <div class="form-group col-md-6">
                                    <label for="tvImage">Upload Image</label>
                                    <input type="file" name="tvImage" class="form-control-file" id="tvImage">
                                    <small class="text-danger"><?= htmlspecialchars($fieldErrors['tvImage']) ?></small>
                                </div>
                                <!-- Submit and Back Buttons -->
                                <div class="form-group col-md-6 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">Add TV</button>
                                    <a href="index.php" class="btn btn-secondary ml-2">Back to Home</a>
                                </div>
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
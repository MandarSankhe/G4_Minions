<?php
// Start the session
session_start();

// Include the database initialization file.
include('dbinit.php');
include('cart.php');

$userid = null;

if (isset($_SESSION['userid'])) {
    // Get the username
    $userid = $_SESSION['userid'];
}

// Calculate the cart count
$cart = new Cart($dbc, $userid);
$cartCount = $cart-> getCartCountFromCookie();

// Define a User class to handle registration and login operations.
class User
{
    private $dbc;

    public function __construct($dbc)
    {
        $this->dbc = $dbc;
    }

    // Register a new user.
    public function register($username, $password, $usertype)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Encrypt password
        $query = "INSERT INTO Users (username, password, usertype) VALUES (?, ?, ?)";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param('sss', $username, $hashedPassword, $usertype);
        return $stmt->execute();
    }

    // Log in an existing user.
    public function login($username, $password)
    {
        $query = "SELECT ID, password, usertype FROM Users WHERE username = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Validate password
            if (password_verify($password, $row['password'])) {
                // Return userid and usertype if password is valid
                return [
                    'userid' => $row['ID'],
                    'usertype' => $row['usertype']
                ];
            }
        }
        return false;
    }

    // Check if a username exists.
    public function userExists($username)
    {
        $query = "SELECT ID FROM Users WHERE username = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}

// Instantiate the User class.
$user = new User($dbc);

// Initialize variables for feedback messages.
$feedback = "";

// Initialize array to store errors
$errors = [];

// Function to check if input is text, numbers and underscores only
function is_text_and_numbers_only($input_value) {
    return preg_match("/^[a-zA-Z0-9_]+$/", $input_value);
}

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {

        // Registration logic
        // Username field validation
        if(!empty($_POST['username'])) {
            $username = $_POST['username'];
            if(!is_text_and_numbers_only($username)) { // Ensure username does not contain special characters
                $errors['reg_username_error'] = "<p>Invalid username format. Please ensure username contains only text, numbers or underscores.</p>";
            }
        } else {
            $errors['reg_username_error'] = "<p>Error! Username is mandatory.</p>";
        }

        // Password field validation
        if(!empty($_POST['password'])) {
            $password = $_POST['password'];
        } else {
            $errors['reg_password_error'] = "<p>Error! Password is mandatory.</p>";
        }

        // User type field validation
        if(!empty($_POST['usertype'])) {
            $usertype = $_POST['usertype'];
        } else {
            $errors['reg_usertype_error'] = "<p>Error! User type is mandatory.</p>";
        }

        if (count($errors) == 0) {
            // Validation successful
            if (!$user->userExists($username)) {
                if ($user->register($username, $password, $usertype)) {
                    $feedback = "<div class='alert alert-success'>Registration successful! Please log in.</div>";
                } else {
                    $feedback = "<div class='alert alert-danger'>Error during registration. Please try again.</div>";
                }
            } else {
                $feedback = "<div class='alert alert-warning'>Username already exists. Please try a different one.</div>";
            }
        }
        else {
            // Display error feedback
            $feedback = "<div class='alert alert-danger'>Please correct errors to register.</div>";
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Login logic
        // Username field validation
        if(!empty($_POST['username'])) {
            $username = $_POST['username'];
            if(!is_text_and_numbers_only($username)) { // Ensure username does not contain special characters
                $errors['login_username_error'] = "<p>Invalid username format. Please ensure username contains only text and numbers.</p>";
            }
        } else {
            $errors['login_username_error'] = "<p>Error! Username is mandatory.</p>";
        }

        // Password field validation
        if(!empty($_POST['password'])) {
            $password = $_POST['password'];
        } else {
            $errors['login_password_error'] = "<p>Error! Password is mandatory.</p>";
        }

        if (count($errors) == 0) {
            // Validation successful
            if ($user->userExists($username)) {
                $userDetails = $user->login($username, $password);
                if ($userDetails) {
                    // Set session variable and redirect
                    $_SESSION['username'] = $username;
                    $_SESSION['userid'] = $userDetails['userid']; // Store userid in session
                    $_SESSION['usertype'] = $userDetails['usertype']; // Store usertype in session

                    // Handle case where user logged in after being redirected from cart_auth.php
                    // Check if the flag redirect_to_cart_auth_after_login is set and redirect to cart_auth.php
                    if (isset($_SESSION['redirect_to_cart_auth_after_login']) && $_SESSION['redirect_to_cart_auth_after_login'] === true) {
                        // Redirect to cart_auth.php
                        header("Location: cart_auth.php");
                    } else {
                        // Redirect to index page
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $feedback = "<div class='alert alert-danger'>Incorrect password. Please try again.</div>";
                }
            } else {
                $feedback = "<div class='alert alert-warning'>Username does not exist. Please register first.</div>";
            }
        }
        else {
            // Display error feedback
            $feedback = "<div class='alert alert-danger'>Please correct errors to login.</div>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="public/CSS/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
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
                        <a class="nav-link" href="cart_page.php">Cart (<?= $cartCount ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <img src="public/Images/login-banner.png" alt="Minions Logo" class="minion-logo">

        <!-- Feedback Section -->
        <div class="feedback-section">
            <?php echo $feedback; ?>
        </div>
        <div class="row">
            <!-- Login Card -->
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label for="login-username" class="form-label">Username</label>
                                <!-- Display any errors on the field -->
                                <div class="error field-error-container">
                                    <?php 
                                        if (!empty($errors['login_username_error'])) { 
                                            echo '<img src="./public/Images/alert-icon.png" class="field-error-icon" />'; 
                                            echo $errors['login_username_error'];
                                        }
                                    ?>
                                </div>
                                <input type="text" class="form-control" id="login-username" name="username">
                            </div>
                            <div class="mb-3">
                                <label for="login-password" class="form-label">Password</label>
                                <!-- Display any errors on the field -->
                                <div class="error field-error-container">
                                    <?php 
                                        if (!empty($errors['login_password_error'])) { 
                                            echo '<img src="./public/Images/alert-icon.png" class="field-error-icon" />'; 
                                            echo $errors['login_password_error'];
                                        }
                                    ?>
                                </div>
                                <input type="password" class="form-control" id="login-password" name="password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Registration Card -->
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">Register</div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="register">
                            <div class="mb-3">
                                <label for="register-username" class="form-label">Username</label>
                                <!-- Display any errors on the field -->
                                <div class="error field-error-container">
                                    <?php 
                                        if (!empty($errors['reg_username_error'])) { 
                                            echo '<img src="./public/Images/alert-icon.png" class="field-error-icon" />'; 
                                            echo $errors['reg_username_error'];
                                        }
                                    ?>
                                </div>
                                <input type="text" class="form-control" id="register-username" name="username" >
                            </div>
                            <div class="mb-3">
                                <label for="register-password" class="form-label">Password</label>
                                <div class="error field-error-container">
                                    <?php 
                                        if (!empty($errors['reg_password_error'])) { 
                                            echo '<img src="./public/Images/alert-icon.png" class="field-error-icon" />'; 
                                            echo $errors['reg_password_error'];
                                        }
                                    ?>
                                </div>
                                <input type="password" class="form-control" id="register-password" name="password" >
                            </div>
                            <div class="mb-3">
                                <label for="usertype" class="form-label">User Type</label><br>
                                <div class="error field-error-container">
                                    <?php 
                                        if (!empty($errors['reg_usertype_error'])) { 
                                            echo '<img src="./public/Images/alert-icon.png" class="field-error-icon" />'; 
                                            echo $errors['reg_usertype_error'];
                                        }
                                    ?>
                                </div>
                                <select class="form-select" id="usertype" name="usertype">
                                    <option value="customer">Customer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
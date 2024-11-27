<?php
// Start the session
session_start();

// Include the database initialization file.
include('dbinit.php');

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
        $query = "SELECT password FROM Users WHERE username = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return password_verify($password, $row['password']); // Validate password
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

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Registration logic
        $username = $_POST['username'];
        $password = $_POST['password'];
        $usertype = $_POST['usertype'];

        if (!$user->userExists($username)) {
            if ($user->register($username, $password, $usertype)) {
                $feedback = "<div class='alert alert-success'>Registration successful! Please log in.</div>";
            } else {
                $feedback = "<div class='alert alert-danger'>Error during registration. Please try again.</div>";
            }
        } else {
            $feedback = "<div class='alert alert-warning'>Username already exists. Please try a different one.</div>";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Login logic
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($user->userExists($username)) {
            if ($user->login($username, $password)) {
                // Set session variable and redirect
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $feedback = "<div class='alert alert-danger'>Incorrect password. Please try again.</div>";
            }
        } else {
            $feedback = "<div class='alert alert-warning'>Username does not exist. Please register first.</div>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minions Themed Login & Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/CSS/style.css" rel="stylesheet">


</head>

<body>
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
                                <input type="text" class="form-control" id="login-username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="login-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="login-password" name="password" required>
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
                                <input type="text" class="form-control" id="register-username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="register-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="register-password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="usertype" class="form-label">User Type</label><br>
                                <select class="form-select" id="usertype" name="usertype" required>
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
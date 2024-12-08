<?php
session_start(); 

// Redirect to login if the user is not logged in or is admin (admin cannot view order history page)
if (!isset($_SESSION['userid']) || ($_SESSION['usertype'] ?? null) == 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include('dbinit.php');

// Define the OrderHistory class
class OrderHistory
{
    private $db;
    private $userId;

    public function __construct($db, $userId)
    {
        $this->db = $db;
        $this->userId = $userId;
    }

    public function fetchOrderHistory()
    {
        $sql = "
            SELECT 
                o.OrderID, 
                o.date, 
                o.total, 
                od.quantity, 
                p.Model, 
                p.Brand, 
                p.Price, 
                p.ImageURL 
            FROM 
                `Order` o
            INNER JOIN 
                OrderDetail od ON o.OrderID = od.OrderID
            INNER JOIN 
                Products p ON od.productID = p.ID
            WHERE 
                o.userid = ?
            ORDER BY 
                o.date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $orderHistory = [];
        while ($row = $result->fetch_assoc()) {
            $orderHistory[] = $row;
        }
        $stmt->close();

        return $orderHistory;
    }
}

// Create an instance of OrderHistory
$orderHistoryObj = new OrderHistory($dbc, $_SESSION['userid']);

// Fetch order history
$orderHistory = $orderHistoryObj->fetchOrderHistory();

// Close the database connection
$dbc->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/CSS/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container nav-custom-container">
            <a class="navbar-brand" href="#">
                <img src="./public/images/logo.png" class="logo" />
                Minions TVstore
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-items">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart_page.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php">Order History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Order History</h2>
        <?php if (empty($orderHistory)): ?>
            <div class="alert alert-info">You have no past orders.</div>
        <?php else: ?>
            <?php 
            $currentOrderId = null;
            foreach ($orderHistory as $item): 
                if ($currentOrderId !== $item['OrderID']): 
                    // Close previous card if a new order starts
                    if ($currentOrderId !== null): ?>
                        </div> <!-- Close card-body -->
                        </div> <!-- Close card -->
                    <?php endif; ?>
                    <!-- Start a new card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Order #<?= htmlspecialchars($item['OrderID']) ?></h5>
                            <p>Date: <?= htmlspecialchars($item['date']) ?></p>
                            <p>Total: $<?= number_format($item['total'], 2) ?></p>
                        </div>
                        <div class="card-body">
                            <?php 
                            $currentOrderId = $item['OrderID'];
                        endif; 
                        ?>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <img src="<?= htmlspecialchars($item['ImageURL']) ?>" class="img-fluid" alt="<?= htmlspecialchars($item['Model']) ?>">
                            </div>
                            <div class="col-md-9">
                                <h5><?= htmlspecialchars($item['Brand']) ?> - <?= htmlspecialchars($item['Model']) ?></h5>
                                <p>Price: $<?= number_format($item['Price'], 2) ?></p>
                                <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div> <!-- Close the last card-body -->
                    </div> <!-- Close the last card -->
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

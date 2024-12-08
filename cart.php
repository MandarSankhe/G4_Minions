<?php

include('television.php');

class Cart
{
    private $dbc;
    private $userId;

    // Constructor
    public function __construct($dbc, $userId = null)
    {
        $this->dbc = $dbc;
        $this->userId = $userId;
    }

    // Add a product to the cart
    public function addToCart($tvId, $quantity)
    {
        // Update cart table in database for logged in users
        if($this->userId) {
            // Check if the product already exists in the user's cart
            $query = "SELECT * FROM Cart WHERE userid = ? AND productid = ?";
            $stmt = $this->dbc->prepare($query);
            $stmt->bind_param("ii", $this->userId, $tvId);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                // If the product exists, update the quantity
                $query = "UPDATE Cart SET quantity = quantity + ? WHERE userid = ? AND productid = ?";
                $stmt = $this->dbc->prepare($query);
                $stmt->bind_param("iii", $quantity, $this->userId, $tvId);
                $stmt->execute();
            } else {
                // If the product doesn't exist, insert
                $query = "INSERT INTO Cart (userid, productid, quantity) VALUES (?, ?, ?)";
                $stmt = $this->dbc->prepare($query);
                $stmt->bind_param("iii", $this->userId, $tvId, $quantity);
                $stmt->execute();
            }
        } else {
            // Use session to store cart details for unauthenticated users
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (isset($_SESSION['cart'][$tvId])) {
                // If the product exists, update the quantity
                $_SESSION['cart'][$tvId] += $quantity;
            } else {
                // If the product doesn't exist, add new product
                $_SESSION['cart'][$tvId] = $quantity;
            }
        }
    }

    // Remove a product from the cart
    public function removeFromCart($tvId)
    {
        // Delete from cart table in database for logged in users
        if($this->userId) {
            $query = "DELETE FROM Cart WHERE userid = ? AND productid = ?";
            $stmt = $this->dbc->prepare($query);
            $stmt->bind_param("ii", $this->userId, $tvId);
            $stmt->execute();
        } else {
            // Remove product from cart in session for unauthenticated users
            if (isset($_SESSION['cart'][$tvId])) {
                unset($_SESSION['cart'][$tvId]);
            }
        }
    }

    // Update the quantity of a product in the cart
    public function updateQuantity($tvId, $quantity)
    {
        // Update quantity in cart table in database for logged in users
        if($this->userId) {
            $query = "UPDATE Cart SET quantity = ? WHERE userid = ? AND productid = ?";
            $stmt = $this->dbc->prepare($query);
            $stmt->bind_param("iii", $quantity, $this->userId, $tvId);
            $stmt->execute();
        } else {
            // Update quantity in session's cart details for unauthenticated users
            if (isset($_SESSION['cart'][$tvId])) {
                $_SESSION['cart'][$tvId] = $quantity;
            }
        }
    }

    // Get all products in the cart for the user
    public function getCartItems()
    {
        $cartItems = [];

        // Get all cart items from database for logged in users
        if($this->userId) {
            $query = "SELECT p.ID, p.Model, p.Brand, p.Price, p.ImageURL, c.quantity 
                      FROM Cart c 
                      INNER JOIN Products p 
                      ON c.productid = p.ID 
                      WHERE c.userid = ?";
            $stmt = $this->dbc->prepare($query);
            $stmt->bind_param("i", $this->userId);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = $row;
            }
        } else {
            // Return all products from session's cart details for unauthenticated users
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $tvId => $quantity) {
                    // Fetch product details from the database, use the getTvByID method from Television class
                    $tv = new Television($this->dbc);
                    $product = $tv->getTvById($tvId);

                    if($product) {
                        $product['quantity'] = $quantity;
                        $cartItems[] = $product;
                    }
                }
            }
        }
        return $cartItems;
    }

    // Get the total price of all products in the cart
    public function getTotalPrice()
    {
        $cartItems = $this->getCartItems();
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['Price'] * $item['quantity'];
        }
        return $total;
    }

    // Insert session cart to database
    public function insertSessionCartToDatabase()
    {
        if ($this->userId && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $tvId => $quantity) {
                // Check if the product already exists in the database cart
                $query = "SELECT * FROM Cart WHERE userid = ? AND productid = ?";
                $stmt = $this->dbc->prepare($query);
                $stmt->bind_param("ii", $this->userId, $tvId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Update quantity
                    $query = "UPDATE Cart SET quantity = quantity + ? WHERE userid = ? AND productid = ?";
                    $stmt = $this->dbc->prepare($query);
                    $stmt->bind_param("iii", $quantity, $this->userId, $tvId);
                    $stmt->execute();
                } else {
                    // Insert new item
                    $query = "INSERT INTO Cart (userid, productid, quantity) VALUES (?, ?, ?)";
                    $stmt = $this->dbc->prepare($query);
                    $stmt->bind_param("iii", $this->userId, $tvId, $quantity);
                    $stmt->execute();
                }
            }
            // Clear the session cart after insert to database
            unset($_SESSION['cart']);
        }
    }
}
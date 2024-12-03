<?php
class Cart
{
    private $dbc;
    private $userId;

    // Constructor
    public function __construct($dbc, $userId)
    {
        $this->dbc = $dbc;
        $this->userId = $userId;
    }

    // Add a product to the cart
    public function addToCart($tvId, $quantity)
    {
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
    }

    // Remove a product from the cart
    public function removeFromCart($tvId)
    {
        $query = "DELETE FROM Cart WHERE userid = ? AND productid = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("ii", $this->userId, $tvId);
        $stmt->execute();
    }

    // Update the quantity of a product in the cart
    public function updateQuantity($tvId, $quantity)
    {
        $query = "UPDATE Cart SET quantity = ? WHERE userid = ? AND productid = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("iii", $quantity, $this->userId, $tvId);
        $stmt->execute();
    }

    // Get all products in the cart for the user
    public function getCartItems()
    {
        $query = "SELECT p.ID, p.Model, p.Brand, p.Price, p.ImageURL, c.quantity 
                  FROM Cart c 
                  INNER JOIN Products p 
                  ON c.productid = p.ID 
                  WHERE c.userid = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
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
}
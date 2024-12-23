<?php

class Television
{
    private $id;
    private $model;
    private $brand;
    private $description;
    private $stock;
    private $price;
    private $imageUrl;
    private $dbc;  // Database connection

    // Constructor
    public function __construct($dbc)
    {
        $this->dbc = $dbc;
    }

    // Getters and setters
    // TV ID
    public function getId() 
    { 
        return $this->id;
    }
    public function setId($id) 
    { 
        $this->id = $id;
    }

    // TV Model
    public function getModel() 
    { 
        return $this->model; 
    }
    public function setModel($model) 
    { 
        $this->model = $model; 
    }

    // TV Brand
    public function getBrand() 
    { 
        return $this->brand; 
    }
    public function setBrand($brand) 
    { 
        $this->brand = $brand; 
    }

    // TV Description
    public function getDescription() 
    { 
        return $this->description; 
    }
    public function setDescription($description) 
    { 
        $this->description = $description; 
    }

    // TV Stock
    public function getStock() 
    { 
        return $this->stock; 
    }
    public function setStock($stock) 
    { 
        $this->stock = $stock; 
    }
    
    // TV Price
    public function getPrice() 
    { 
        return $this->price; 
    }
    public function setPrice($price) 
    { 
        $this->price = $price; 
    }
    
    // TV Image URL
    public function getImageUrl() 
    { 
        return $this->imageUrl; 
    }
    public function setImageUrl($imageUrl) 
    { 
        $this->imageUrl = $imageUrl; 
    }

    // Insert new TV into db
    public function insertTv()
    {
        $query = "INSERT INTO Products (Model, Brand, Description, Stock, Price, ImageURL) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("ssssds", $this->model, $this->brand, $this->description, $this->stock, $this->price, $this->imageUrl);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error inserting TV: " . $stmt->error;
        }
    }

    // Update existing TV in db
    public function updateTv()
    {
        $query = "UPDATE Products SET Model = ?, Brand = ?, Description = ?, Stock = ?, Price = ?, ImageURL = ? 
                  WHERE ID = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("ssssdsi", $this->model, $this->brand, $this->description, $this->stock, $this->price, $this->imageUrl, $this->id);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error updating TV: " . $stmt->error;
        }
    }

    // Delete TV from db
    public function deleteTv()
    {
        $query = "DELETE FROM Products WHERE ID = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("i", $this->id);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error deleting TV: " . $stmt->error;
        }
    }

    // Get TV by ID
    public function getTvById($id)
    {
        $query = "SELECT * FROM Products WHERE ID = ?";
        $stmt = $this->dbc->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Get all TVs from db with filter conditions
    public function getAllTVs($searchQuery, $stockFilter, $sortOrder)
    {

        $query = "SELECT * FROM Products";
        $whereClause = ""; // String to hold filter conditions

        // 1. Search filter on Model, Description or Brand column
        if (!empty($searchQuery)) {
            $searchQueryEscaped = $this->dbc->real_escape_string($searchQuery);
            $whereClause .= "(Model LIKE '%$searchQueryEscaped%' 
                              OR Description LIKE '%$searchQueryEscaped%' 
                              OR Brand LIKE '%$searchQueryEscaped%')";
        }

        // 2. Add condition for stockFilter
        if (!empty($stockFilter)) {
            if (!empty($whereClause)) {
                $whereClause .= " AND ";
            }
            $whereClause .= "Stock = '" . $this->dbc->real_escape_string($stockFilter) . "'";
        }

        // Append WHERE clause to the query if any conditions exist
        if (!empty($whereClause)) {
            $query .= " WHERE " . $whereClause;
        }

        // 3. Apply sorting based on sortOrder
        switch ($sortOrder) {
            case 'price_asc':
                $query .= " ORDER BY Price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY Price DESC";
                break;
            default:
                // No sorting
                break;
        }

        // Execute query
        $result = $this->dbc->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

}
?>

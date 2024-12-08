<?php
// Defining constants for database connection parameters.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'G4_Minions');

// Creating a new connection to MySQL Server.
$dbc = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check if the connection is able to talk to MySQL Server.
if ($dbc->connect_error) {
    die("Connection failed: " . $dbc->connect_error);
}

// Creating a new database.
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($dbc->query($sql) === TRUE) {
    echo "<script>console.log('Database \"" . DB_NAME . "\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating database: " . $dbc->error . "');</script>";
}

// Selecting the created or existing database to use for subsequent queries.
$dbc->select_db(DB_NAME);

// Creating `Users` table.
$users_table = "CREATE TABLE IF NOT EXISTS Users (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    usertype ENUM('admin', 'customer') NOT NULL,
    firstname VARCHAR(100) DEFAULT NULL,
    lastname VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL
)";
if ($dbc->query($users_table) === TRUE) {
    echo "<script>console.log('Table \"Users\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating Users table: " . $dbc->error . "');</script>";
}

// Creating `Products` table.
$products_table = "CREATE TABLE IF NOT EXISTS Products (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Model VARCHAR(100) NOT NULL,
    Brand VARCHAR(100) NOT NULL,
    Description TEXT NOT NULL,
    Stock VARCHAR(50) NOT NULL CHECK (Stock IN ('instock', 'preorder')),
    Price DECIMAL(10, 2) NOT NULL,
    ImageURL VARCHAR(255) DEFAULT NULL
)";
if ($dbc->query($products_table) === TRUE) {
    echo "<script>console.log('Table \"Products\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating Products table: " . $dbc->error . "');</script>";
}

// Creating `Cart` table.
$cart_table = "CREATE TABLE IF NOT EXISTS Cart (
    userid INT NOT NULL,
    productid INT NOT NULL,
    quantity INT NOT NULL,
    PRIMARY KEY (userid, productid),
    FOREIGN KEY (userid) REFERENCES Users(ID),
    FOREIGN KEY (productid) REFERENCES Products(ID)
)";
if ($dbc->query($cart_table) === TRUE) {
    echo "<script>console.log('Table \"Cart\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating Cart table: " . $dbc->error . "');</script>";
}

// Creating `Order` table.
$order_table = "CREATE TABLE IF NOT EXISTS `Order` (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    date DATETIME NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (userid) REFERENCES Users(ID)
)";
if ($dbc->query($order_table) === TRUE) {
    echo "<script>console.log('Table \"Order\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating Order table: " . $dbc->error . "');</script>";
}

// Creating `OrderDetail` table.
$order_detail_table = "CREATE TABLE IF NOT EXISTS OrderDetail (
    OrderID INT NOT NULL,
    productID INT NOT NULL,
    quantity INT NOT NULL,
    PRIMARY KEY (OrderID, productID),
    FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID),
    FOREIGN KEY (productID) REFERENCES Products(ID)
)";
if ($dbc->query($order_detail_table) === TRUE) {
    echo "<script>console.log('Table \"OrderDetail\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating OrderDetail table: " . $dbc->error . "');</script>";
}

// Add TV products to the Products table
// Check if table is empty before inserting TV products
$count_products = "SELECT COUNT(*) as count FROM Products";
$result = $dbc->query($count_products);

if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // Insert TV Products
        $product_insert_query = " INSERT INTO `Products` (`Model`, `Brand`, `Description`, `Stock`, `Price`, `ImageURL`) VALUES
        ('UHD Smart TV', 'LG', '65-inch with HDR10 Pro for optimized brightness levels, vivid color and remarkable detail. A5 AI Processor 4K Gen6 for an immersive experience.', 'instock', 999.99, 'https://i.imgur.com/HXZNXw5.jpeg'),
        ('MiniLed QNED Smart TV', 'LG', 'Crisp 4K Ultra HD with dimming zones for sharp clarity, powered by the advanced Alpha 8 AI Processor for ultimate immersion.', 'instock', 2399.99, 'https://i.imgur.com/iyHSNoz.jpeg'),
        ('QLED Smart TV', 'Samsung', 'Experience stunning 4K visuals on a 55-inch display with a 120Hz refresh rate, NQ4 AI Gen2 processing, and Dolby Atmos audio.', 'instock', 998.00, 'https://i.imgur.com/y25XcLg.jpeg'),
        ('Crystal UHD Smart TV 55-inch', 'Samsung', 'Immerse in vibrant visuals, featuring PurColour and 4K upscaling. Lifelike viewing and entertainment with a built-in Gaming Hub.', 'preorder', 899.99, 'https://i.imgur.com/Lo9h43k.jpeg'),
        ('Bravia 3 Smart TV', 'Sony', 'Enjoy stress-free viewing with our 43-inch 4K Ultra HD LED Smart TV, offering vivid visuals, dynamic sound, and all-in-one smart features.', 'instock', 699.99, 'https://i.imgur.com/s6OxlHs.jpeg'),
        ('QLED Smart TV', 'Philips', '4K Display with Dolby Vision and HDR10 for vibrant colors. 150,000+ streaming options on a sleek Roku Smart TV with a borderless design.', 'instock', 1299.99, 'https://i.imgur.com/E6FQg5G.jpeg'),
        ('X77L Google TV', 'Sony', 'Lifelike 4K HDR visuals powered by 4K Processor X1. 65-inch Google TV with Assistant and upscales HD content to near-4K clarity.', 'instock', 949.00, 'https://i.imgur.com/MhiQTaW.jpeg'),
        ('Crystal UHD Smart TV 98-inch', 'Samsung', '98-inch DU9000 4K Smart TV for a cinema-like experience. Vibrant visuals, optimal gaming, smart features for total immersion.', 'preorder', 2298.00, 'https://i.imgur.com/bemZrF7.jpeg');";

        if ($dbc->query($product_insert_query) == TRUE) {
            echo "<script>console.log('TV Products added successfully');</script>";
        } else {
            echo "<script>console.log('Error adding TV products: " . $dbc->error . "');</script>";
        }
    } else {
        echo "<script>console.log('Products table already contains TVs.');</script>";
    }
} else {
    echo "<script>console.log('Error checking Products table: " . $dbc->error . "');</script>";
}

?>

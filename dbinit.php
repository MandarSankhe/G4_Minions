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
    ProductName VARCHAR(100) NOT NULL,
    Brand VARCHAR(100) NOT NULL,
    Description TEXT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL
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

?>

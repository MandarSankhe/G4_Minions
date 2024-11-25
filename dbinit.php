<?php
// Defining constants for database connection parameters.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'bookstore_mandar');

// Creating a new connection to MySQL Server.
$dbc = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check if the connection is able to talk to MySQL Server.
if ($dbc->connect_error) {
    die("Connection failed: " . $dbc->connect_error);
}

// Creating a new database.
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($dbc->query($sql) === TRUE) {
    // Logging the echo as a console statement for browser just to make sure if the database is created or not.
    echo "<script>console.log('Database \"" . DB_NAME . "\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating database: " . $dbc->error . "');</script>";
}

// Selecting the created or existing database to use for subsequent queries.
$dbc->select_db(DB_NAME);

// Creating books table with the specified columns.
$table_query = "CREATE TABLE IF NOT EXISTS books (
    BookID INT AUTO_INCREMENT PRIMARY KEY,
    BookName VARCHAR(100) NOT NULL,
    Author VARCHAR(100) NOT NULL,
    BookDescription TEXT NOT NULL,
    QuantityAvailable INT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    ProductAddedBy VARCHAR(100) NOT NULL DEFAULT 'Mandar'
)";

// Executing the query to create the table and logging in browser's console for success.
if ($dbc->query($table_query) === TRUE) {
    echo "<script>console.log('Table \"books\" created successfully');</script>";
} else {
    echo "<script>console.log('Error creating table: " . $dbc->error . "');</script>";
}

?>

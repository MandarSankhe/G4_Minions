-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bookstore_mandar;



-- Use the database
USE bookstore_mandar;

-- Create table 'books' if it doesn't exist
CREATE TABLE IF NOT EXISTS books (
    BookID INT AUTO_INCREMENT PRIMARY KEY,
    BookName VARCHAR(100) NOT NULL,
    Author VARCHAR(100) NOT NULL,
    BookDescription TEXT NOT NULL,
    QuantityAvailable INT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    ProductAddedBy VARCHAR(100) NOT NULL DEFAULT 'Mandar'
);

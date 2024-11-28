-- Create the database if it does not exist
CREATE DATABASE IF NOT EXISTS G4_Minions;

-- Use the database
USE G4_Minions;

-- Create the Users table
CREATE TABLE IF NOT EXISTS Users (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    usertype ENUM('admin', 'customer') NOT NULL,
    firstname VARCHAR(100) DEFAULT NULL,
    lastname VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL
);

-- Create the Products table
CREATE TABLE IF NOT EXISTS Products (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Model VARCHAR(100) NOT NULL,
    Brand VARCHAR(100) NOT NULL,
    Description TEXT NOT NULL,
    Stock VARCHAR(50) NOT NULL CHECK (Stock IN ('instock', 'preorder')),
    Price DECIMAL(10, 2) NOT NULL,
    ImageURL VARCHAR(255) DEFAULT NULL

);

-- Create the Cart table
CREATE TABLE IF NOT EXISTS Cart (
    userid INT NOT NULL,
    productid INT NOT NULL,
    quantity INT NOT NULL,
    PRIMARY KEY (userid, productid),
    FOREIGN KEY (userid) REFERENCES Users(ID),
    FOREIGN KEY (productid) REFERENCES Products(ID)
);

-- Create the Order table
CREATE TABLE IF NOT EXISTS `Order` (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    date DATETIME NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (userid) REFERENCES Users(ID)
);

-- Create the OrderDetail table
CREATE TABLE IF NOT EXISTS OrderDetail (
    OrderID INT NOT NULL,
    productID INT NOT NULL,
    quantity INT NOT NULL,
    PRIMARY KEY (OrderID, productID),
    FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID),
    FOREIGN KEY (productID) REFERENCES Products(ID)
);

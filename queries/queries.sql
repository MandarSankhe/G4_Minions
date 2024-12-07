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
CREATE TABLE IF NOT EXISTS Order (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    date DATETIME NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    shipping_address_id INT NOT NULL,
    FOREIGN KEY (userid) REFERENCES Users(ID),
    FOREIGN KEY (shipping_address_id) REFERENCES Addresses(ID)
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


-- Create the Address Table
CREATE TABLE IF NOT EXISTS Addresses (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(ID)
);




-- Insert tv data in products table 
INSERT INTO `products` (`Model`, `Brand`, `Description`, `Stock`, `Price`, `ImageURL`) VALUES
('UHD Smart TV', 'LG', '65-inch with HDR10 Pro for optimized brightness levels, vivid color and remarkable detail. A5 AI Processor 4K Gen6 for an immersive experience.', 'instock', 999.99, 'lg_1.png'),
('MiniLed QNED Smart TV', 'LG', 'Crisp 4K Ultra HD with dimming zones for sharp clarity, powered by the advanced Alpha 8 AI Processor for ultimate immersion.', 'instock', 2399.99, 'lg_2.png'),
('QLED Smart TV', 'Samsung', 'Experience stunning 4K visuals on a 55-inch display with a 120Hz refresh rate, NQ4 AI Gen2 processing, and Dolby Atmos audio.', 'instock', 998.00, 'samsung_1.png'),
('Crystal UHD Smart TV 55-inch', 'Samsung', 'Immerse in vibrant visuals, featuring PurColour and 4K upscaling. Lifelike viewing and entertainment with a built-in Gaming Hub.', 'preorder', 899.99, 'samsung_2.png'),
('Bravia Smart TV', 'Sony', 'Enjoy stress-free viewing with our 43â€ 4K Ultra HD LED Smart TV, offering vivid visuals, dynamic sound, and all-in-one smart features.', 'instock', 699.99, 'sony_1.png'),
('QLED Smart TV', 'Philips', '4K Display with Dolby Vision and HDR10 for vibrant colors. 150,000+ streaming options on a sleek Roku Smart TV with a borderless design.', 'instock', 1299.99, 'philips_1.png'),
('X77L Google TV', 'Sony', 'Lifelike 4K HDR visuals powered by 4K Processor X1â„¢. 65-inch Google TV with Assistant and upscales HD content to near-4K clarity.', 'instock', 949.00, 'sony_2.png'),
('Crystal UHD Smart TV 98-inch', 'Samsung', '98-inch DU9000 4K Smart TV for a cinema-like experience. Vibrant visuals, optimal gaming, smart features for total immersion.', 'preorder', 2298.00, 'samsung_3.png');

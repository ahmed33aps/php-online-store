-- Reconstructed from the supplied PHP queries. This is a fresh development schema,
-- not a recovered copy of the original database. No real customer data is included.
CREATE DATABASE IF NOT EXISTS online_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_store;
CREATE TABLE users (
 User_ID INT AUTO_INCREMENT PRIMARY KEY,
 User_Name VARCHAR(64) NOT NULL UNIQUE,
 First_Name VARCHAR(100) NOT NULL,
 Last_Name VARCHAR(100) NOT NULL,
 Sex VARCHAR(20) NOT NULL DEFAULT '',
 Register_Date DATE NOT NULL,
 password VARCHAR(255) NOT NULL,
 Email VARCHAR(254) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE customers (
 Customer_ID INT PRIMARY KEY, points INT NOT NULL DEFAULT 0,
 FOREIGN KEY(Customer_ID) REFERENCES users(User_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE administrators (
 Admin_ID INT PRIMARY KEY,
 FOREIGN KEY(Admin_ID) REFERENCES users(User_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE agents (
 Agent_ID INT PRIMARY KEY, Office VARCHAR(100) NOT NULL DEFAULT '',
 FOREIGN KEY(Agent_ID) REFERENCES users(User_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE categories (
 Category_Name VARCHAR(100) PRIMARY KEY, Description TEXT,
 Admin_ID INT NOT NULL,
 FOREIGN KEY(Admin_ID) REFERENCES administrators(Admin_ID)
) ENGINE=InnoDB;
CREATE TABLE products (
 Product_ID INT AUTO_INCREMENT PRIMARY KEY,
 Product_Name VARCHAR(150) NOT NULL, Description TEXT,
 Current_Price DECIMAL(12,2) NOT NULL, stock INT NOT NULL DEFAULT 0,
 Category_Name VARCHAR(100) NOT NULL, Admin_ID INT NOT NULL,
 CHECK(Current_Price>=0), CHECK(stock>=0),
 FOREIGN KEY(Category_Name) REFERENCES categories(Category_Name) ON UPDATE CASCADE,
 FOREIGN KEY(Admin_ID) REFERENCES administrators(Admin_ID)
) ENGINE=InnoDB;
CREATE TABLE orders (
 Order_ID INT AUTO_INCREMENT PRIMARY KEY,
 Order_Date DATE NOT NULL,
 Status ENUM('Preparing','Waiting','Prepared','IN Delivery','Delivered','Declined') NOT NULL,
 Customer_ID INT NOT NULL,
 FOREIGN KEY(Customer_ID) REFERENCES customers(Customer_ID)
) ENGINE=InnoDB;
CREATE TABLE order_items (
 Order_ID INT NOT NULL, Product_ID INT NOT NULL,
 unit_price DECIMAL(12,2) NOT NULL, quantity INT NOT NULL, total_price DECIMAL(14,2) NOT NULL,
 PRIMARY KEY(Order_ID,Product_ID), CHECK(quantity>0), CHECK(unit_price>=0), CHECK(total_price>=0),
 FOREIGN KEY(Order_ID) REFERENCES orders(Order_ID) ON DELETE CASCADE,
 FOREIGN KEY(Product_ID) REFERENCES products(Product_ID)
) ENGINE=InnoDB;
CREATE TABLE orderstatushistory (
 History_ID INT AUTO_INCREMENT PRIMARY KEY,
 Old_Status VARCHAR(30) NOT NULL, New_Status VARCHAR(30) NOT NULL,
 Update_Date DATE NOT NULL, Customer_ID INT NOT NULL, Agent_ID INT NULL, Order_ID INT NOT NULL,
 FOREIGN KEY(Customer_ID) REFERENCES customers(Customer_ID),
 FOREIGN KEY(Agent_ID) REFERENCES agents(Agent_ID) ON DELETE SET NULL,
 FOREIGN KEY(Order_ID) REFERENCES orders(Order_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
-- Legacy cleanup code references these tables. The supplied project does not
-- implement a payment processor or checkout; no card data should be stored.
CREATE TABLE payments (
 Payment_ID INT AUTO_INCREMENT PRIMARY KEY, Order_ID INT NOT NULL,
 FOREIGN KEY(Order_ID) REFERENCES orders(Order_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE cash (
 Payment_ID INT PRIMARY KEY,
 FOREIGN KEY(Payment_ID) REFERENCES payments(Payment_ID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE card (
 Payment_ID INT PRIMARY KEY,
 FOREIGN KEY(Payment_ID) REFERENCES payments(Payment_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE DATABASE IF NOT EXISTS ceylon_spice_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ceylon_spice_hub;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS reviews, payments, order_items, orders, cart_items, carts, subscribers, messages, products, categories, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 user_id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(150) UNIQUE NOT NULL,
 password VARCHAR(255) NOT NULL,
 contact VARCHAR(20),
 address VARCHAR(255),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
 category_id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 short_name VARCHAR(50) NOT NULL,
 slug VARCHAR(50) UNIQUE NOT NULL,
 tagline VARCHAR(100),
 image VARCHAR(255)
);

CREATE TABLE products (
 product_id INT AUTO_INCREMENT PRIMARY KEY,
 category_id INT NOT NULL,
 name VARCHAR(150) NOT NULL,
 slug VARCHAR(160) UNIQUE NOT NULL,
 description TEXT,
 price DECIMAL(10,2) NOT NULL,
 weight VARCHAR(30),
 stock INT NOT NULL DEFAULT 0,
 badge VARCHAR(30),
 default_rating DECIMAL(2,1) DEFAULT 4.5,
 image VARCHAR(255),
 featured TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(category_id) REFERENCES categories(category_id)
);

CREATE TABLE carts (
 cart_id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 session_token VARCHAR(64) UNIQUE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE cart_items (
 cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
 cart_id INT NOT NULL,
 product_id INT NOT NULL,
 quantity INT NOT NULL DEFAULT 1,
 UNIQUE KEY uq_cart_product(cart_id,product_id),
 FOREIGN KEY(cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
 FOREIGN KEY(product_id) REFERENCES products(product_id)
);

CREATE TABLE orders (
 order_id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 customer_name VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL,
 contact VARCHAR(20) NOT NULL,
 date DATETIME DEFAULT CURRENT_TIMESTAMP,
 address VARCHAR(255) NOT NULL,
 city VARCHAR(100) NOT NULL,
 total_amount DECIMAL(10,2) NOT NULL,
 status VARCHAR(30) DEFAULT 'Pending',
 FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE order_items (
 order_item_id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 product_id INT NOT NULL,
 quantity INT NOT NULL,
 price DECIMAL(10,2) NOT NULL,
 FOREIGN KEY(order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
 FOREIGN KEY(product_id) REFERENCES products(product_id)
);

CREATE TABLE payments (
 payment_id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 date DATETIME DEFAULT CURRENT_TIMESTAMP,
 amount DECIMAL(10,2) NOT NULL,
 method VARCHAR(50) DEFAULT 'Cash on Delivery',
 transaction_id VARCHAR(100),
 FOREIGN KEY(order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE reviews (
 review_id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 product_id INT NOT NULL,
 rating TINYINT NOT NULL,
 comment TEXT,
 date DATETIME DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE SET NULL,
 FOREIGN KEY(product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

CREATE TABLE subscribers (
 subscriber_id INT AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(150) UNIQUE NOT NULL,
 subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
 message_id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL,
 message TEXT NOT NULL,
 sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories(name,short_name,slug,tagline,image) VALUES
('Ceylon Cinnamon','Cinnamon','cinnamon','Warm & Sweet','images/cinnamon.jpg'),
('Black Pepper','Pepper','pepper','Bold & Aromatic','images/pepper.jpg'),
('Cardamom','Cardamom','cardamom','Rich & Fragrant','images/cardamom.jpg'),
('Cloves','Cloves','cloves','Strong & Pungent','images/cloves.jpg'),
('Turmeric','Turmeric','turmeric','Golden & Earthy','images/turmeric.jpg'),
('Nutmeg','Nutmeg','nutmeg','Exotic & Aromatic','images/nutmeg.jpg');

INSERT INTO products(category_id,name,slug,description,price,weight,stock,badge,default_rating,image,featured) VALUES
(1,'Ceylon Cinnamon Sticks','ceylon-cinnamon-sticks','Premium true Ceylon cinnamon sticks with a delicate aroma and naturally sweet flavour. Ideal for tea, desserts, curries and everyday cooking.',1250.00,'50g',50,'BEST SELLER',4.9,'images/cinnamon-product.jpg',1),
(2,'Premium Black Pepper','premium-black-pepper','Aromatic Sri Lankan black peppercorns selected for a bold flavour and fresh fragrance.',980.00,'100g',40,'POPULAR',4.8,'images/pepper-product.jpg',1),
(3,'Green Cardamom','green-cardamom','Fragrant premium green cardamom for tea, desserts, rice dishes and traditional Sri Lankan recipes.',1850.00,'50g',30,'PREMIUM',4.7,'images/cardamom-product.jpg',1),
(4,'Premium Cloves','premium-cloves','Whole Sri Lankan cloves with a rich aroma and warm, spicy flavour.',780.00,'50g',45,NULL,4.8,'images/cloves-product.jpg',1),
(5,'Pure Turmeric Powder','pure-turmeric-powder','Fine golden turmeric powder for curries, rice, drinks and everyday recipes.',650.00,'100g',60,'VALUE PICK',4.5,'images/turmeric-product.jpg',1),
(6,'Ceylon Nutmeg','ceylon-nutmeg','Premium Ceylon nutmeg with an intense warm aroma, perfect for sweet and savoury dishes.',890.00,'25g',35,NULL,4.4,'images/nutmeg-product.jpg',1);

INSERT INTO reviews(product_id,rating,comment) VALUES
(1,5,'Excellent quality and lovely aroma.'),
(1,5,'Very fresh and authentic.'),
(2,5,'Great aroma and flavour.'),
(2,4,'Good quality pepper.'),
(3,5,'Very fragrant cardamom.'),
(4,5,'Fresh and aromatic cloves.'),
(5,5,'Good colour and flavour.'),
(6,4,'Nice aroma and good quality.');

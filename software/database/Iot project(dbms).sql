CREATE DATABASE smart_shop;
USE smart_shop;
drop database smart_shop;
CREATE TABLE products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  barcode VARCHAR(20) UNIQUE NOT NULL,
  name VARCHAR(100),
  brand VARCHAR(50),
  category VARCHAR(50),
  mrp DECIMAL(8,2),
  discount DECIMAL(5,2),
  final_price DECIMAL(8,2),
  pack_size VARCHAR(20),
  expiry_date DATE,
  sponsored_flag BOOLEAN DEFAULT 0,
  store_margin DECIMAL(5,2)
);

CREATE TABLE bundles (
  bundle_id INT AUTO_INCREMENT PRIMARY KEY,
  bundle_name VARCHAR(50),
  total_price DECIMAL(8,2),
  discount_text VARCHAR(100)
);

CREATE TABLE bundle_items (
  bundle_item_id INT AUTO_INCREMENT PRIMARY KEY,
  bundle_id INT,
  product_id INT,
  FOREIGN KEY (bundle_id) REFERENCES bundles(bundle_id),
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE customers (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(15)
);

CREATE TABLE cart (
  cart_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT,
  product_id INT,
  quantity INT,
  price DECIMAL(8,2),
  mode_selected VARCHAR(20),
  timestamp DATETIME,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE promotions (
  promo_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  promo_type VARCHAR(20),
  discount_percent DECIMAL(5,2),
  valid_till DATE,
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE transactions (
  transaction_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT,
  total_amount DECIMAL(8,2),
  mode_selected VARCHAR(20),
  payment_status VARCHAR(20),
  timestamp DATETIME,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

ALTER TABLE promotions 
ADD FOREIGN KEY (product_id) REFERENCES products(product_id);

ALTER TABLE cart 
ADD FOREIGN KEY (product_id) REFERENCES products(product_id);

ALTER TABLE bundle_items 
ADD FOREIGN KEY (bundle_id) REFERENCES bundles(bundle_id),
ADD FOREIGN KEY (product_id) REFERENCES products(product_id);

INSERT INTO customers (name, email, phone) VALUES
('Chinmayee', 'chinmayee@gmail.com', '9876543210'),
('Rahul', 'rahul@gmail.com', '9123456789');

INSERT INTO products (barcode, name, brand, category, mrp, discount, final_price, pack_size, expiry_date, sponsored_flag, store_margin) VALUES
('1001', 'Dove Shampoo 180ml', 'Unilever', 'Haircare', 250.00, 10.00, 225.00, '180ml', '2026-01-15', 0, 12.5),
('1002', 'Clinic Plus Shampoo 180ml', 'HUL', 'Haircare', 220.00, 5.00, 209.00, '180ml', '2025-12-10', 0, 10.0),
('1003', 'Pears Soap 100g', 'Unilever', 'Skincare', 80.00, 10.00, 72.00, '100g', '2026-05-30', 1, 15.0),
('1004', 'Colgate Paste 200g', 'Colgate', 'Oralcare', 150.00, 15.00, 127.50, '200g', '2026-04-25', 0, 8.0);

INSERT INTO promotions (product_id, promo_type, discount_percent, valid_till) VALUES
(1, 'Festive Offer', 15.00, '2025-12-31'),
(3, 'Buy 2 Get 1', 0.00, '2025-11-30');

INSERT INTO bundles (bundle_name, total_price, discount_text) VALUES
('Daily Care Combo', 380.00, 'Buy Dove Shampoo + Pears Soap and Save ₹45'),
('Oral & Skin Combo', 180.00, 'Colgate + Pears Combo – Save ₹20');

INSERT INTO bundle_items (bundle_id, product_id) VALUES
(1, 1),  -- Dove Shampoo
(1, 3),  -- Pears Soap
(2, 3),  -- Pears Soap
(2, 4);  -- Colgate Paste

INSERT INTO cart (customer_id, product_id, quantity, price, mode_selected, timestamp) VALUES
(1, 1, 1, 225.00, 'Economy', NOW()),
(1, 3, 2, 144.00, 'Value', NOW()),
(2, 4, 1, 127.50, 'Premium', NOW());

INSERT INTO transactions (customer_id, total_amount, mode_selected, payment_status, timestamp) VALUES
(1, 369.00, 'Value', 'Paid', NOW()),
(2, 127.50, 'Premium', 'Pending', NOW());






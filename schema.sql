-- 1. Users (Admin login)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'cashier') DEFAULT 'cashier',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 2. Categories (e.g., Pizza, Burger, Drink)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- 3. Products (Menu items like Margherita Pizza, Zinger Burger)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 4. Ingredients (inventory items like cheese, chicken, bun)
CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20), -- grams, ml, pcs
    cost_per_unit DECIMAL(10,2),
    threshold DECIMAL(10,2) DEFAULT 0
);

-- 5. Recipes (Map product to ingredients)
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    ingredient_id INT,
    quantity_required DECIMAL(10,2), -- per one unit of product
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id)
);

-- 6. Sales (orders placed)
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_price DECIMAL(10,2),
    order_type ENUM('dine-in', 'takeaway', 'delivery') DEFAULT 'dine-in',
    delivery_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Sale Items (products in each sale)
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    product_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 7. Customers (customers of online orders)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Invoice (create invoice of orders)
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    invoice_number VARCHAR(100),
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id)
);

-- 7. Employees (To manage employees)
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    role VARCHAR(50),
    salary DECIMAL(10,2),
    join_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

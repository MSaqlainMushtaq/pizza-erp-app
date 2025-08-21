# Pizza-erp-app

A lightweight Enterprise Resource Planning (ERP) system for a Pizza Shop. This web-based application helps manage products, categories, recipes, sales, inventory, expenses, employees, customers, and reports in one place.

## Features

Authentication System (Admin login/signup)  
Dashboard with summary of sales, inventory, and profits  
Product Management (Add/Edit/Delete products)  
Category Management (Organize products by categories)  
Inventory Management (Track ingredients, units, stock in/out)  
Sales Module (Create new sales, Auto-deduct inventory on sale, Generate invoices)  
Recipe Management (Assign ingredients to products with quantities)  
Expense Tracking  
Employee Management  
Deals Management (Combo offers, discounts)  
Reports{  
    Sales report (view/export to PDF)  
    Inventory report (view/export to Excel/PDF)  
    Customer Dashboard (for order history & profile)  
    }  

## Project Structure
pizza-erp-app/  
│  
├── assets/             # CSS, JS, Images  
├── config/             # Database config  
├── controllers/        # Business logic (PHP)  
├── includes/           # Header, Footer, Navbar  
├── libs/               # Libraries (TCPDF, PhpSpreadsheet)  
├── views/              # UI files (auth, sales, inventory, reports, etc.)  
└── schema.sql          # Database schema  

## Tech Stack

Frontend: HTML, CSS, JavaScript   
Backend: PHP (Core PHP, Modular MVC-style structure)  
Database: MySQL  
Libraries:{  
&nbsp;&nbsp;&nbsp;&nbsp;PhpSpreadsheet  
&nbsp;&nbsp;&nbsp;&nbsp; → Excel export  
&nbsp;&nbsp;&nbsp;&nbsp;TCPDF  
&nbsp;&nbsp;&nbsp;&nbsp; → PDF export  
&nbsp;&nbsp;&nbsp;&nbsp;}  

## Installation

### Clone the repository

git clone https://github.com/MSaqlainMushtaq/pizza-erp-app.git  
cd pizza-erp-app  

### Import database

Open phpMyAdmin (or MySQL CLI)  
Create a database ( pizza_erp_db)  
Import Pizza_erp_db.sql  

### Configure database connection

Open config/db.php  

Update DB credentials:  

$host = "localhost";  
$user = "root";  
$pass = "";  
$dbname = "pizza_erp_db";  


### Start development server (XAMPP, WAMP, or built-in PHP server)

php -S localhost:8000  


### Open in browser

http://localhost/pizza-erp-app/views/auth/login.php

### Default Login

Admin User  
Username: Admin  
Password: admin@123  
(You can change this in users module after login go to update user)  

## Future Improvements

Delivery module (online orders & rider tracking)  
Customer feedback system  
Multi-branch support  
REST API for mobile app integration  

## Contributing

Fork this repo  
Create a new branch (feature/my-feature)  
Commit your changes  
Push and open a pull request  

## License

This project is licensed under the MIT License – you are free to use, modify, and distribute with attribution.

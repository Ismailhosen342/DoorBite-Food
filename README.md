 
# DoorBite Food! 🍟🍫  
Snack & Food Inventory and Sales Management System

DoorBite Food! is a web-based inventory and sales management system designed for small shops, cafeterias, or snack businesses.  
It helps manage products, stock, sales, bulk selling, reports, and user access through a simple dashboard.

---

## ✨ Features

### 🔐 Authentication
- Login & Signup system
- Role-based users (Admin / Staff)
- Secure session handling
- Logout functionality

### 📊 Dashboard
- Today’s revenue
- Net profit calculation
- Total products count
- Low stock alerts
- Recent sales overview

### 🛒 Sales
- Point-of-sale (POS) style sales page
- Add products to cart
- Automatic stock deduction
- Prevents selling out-of-stock items

### 📦 Bulk Sell
- Sell multiple products at once
- Enter **current stock (counted)** for each product
- System automatically calculates sold quantity
- Updates stock, sales, and inventory logs in one action

### 🧾 Products
- Add, edit, and soft-delete products
- Buying price & selling price
- Stock tracking
- Low stock highlighting

### 📋 Inventory Logs
- Track all stock changes
- Reasons: restock, adjustment, sale
- Shows old stock vs new stock
- Product names displayed correctly

### 📈 Reports
- Total revenue
- Total cost
- Net profit
- Last 7 days sales chart
- Items sold statistics
- CSV export option

### ⚙️ Settings
- User information
- System configuration (expandable)

---

## 🧱 Tech Stack

- **Backend:** PHP (PDO)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Charts:** Chart.js
- **Server:** Apache / Nginx
- **Authentication:** PHP Sessions

---
/
├── config/
│   └── database.php      # Database connection and session handling
├── includes/
│   ├── header.php        # HTML header template
│   ├── sidebar.php       # Navigation sidebar
│   └── footer.php        # HTML footer template
├── api/
│   ├── products.php      # Product CRUD API
│   ├── sales.php         # Sales processing API
│   ├── inventory.php     # Inventory management API
│   ├── settings.php      # Settings API
│   └── export.php        # CSV export
├── css/
│   └── style.css         # Main stylesheet
├── js/
│   └── app.js            # JavaScript utilities
├── index.php             # Redirect to login
├── login.php             # Login page
├── logout.php            # Session logout
├── dashboard.php         # Dashboard page
├── sales.php             # Sales POS page
├── products.php          # Products management
├── inventory.php         # Inventory logs
├── reports.php           # Sales reports
└── settings.php          # App settings

## 🗄️ Database Structure

Main tables used:
- `users`
- `products`
- `sales`
- `sale_items`
- `inventory_logs`

All product stock changes are logged in `inventory_logs`.

---

## 🚀 Installation Guide

### 1️⃣ Clone or Upload Project
```bash
git clone https://github.com/your-username/doorbite-food.git
````

or upload files to your server.

---

### 2️⃣ Create Database

Create a MySQL database and import your SQL structure.

Example:

```sql
CREATE DATABASE doorbite;
```

---

### 3️⃣ Configure Database

Edit file:

```
config/database.php
```

```php
$host = 'localhost';
$dbname = 'doorbite';
$user = 'your_db_user';
$password = 'your_db_password';
```

---

### 4️⃣ Run Project

Open in browser:

```
http://localhost/doorbite/
```

---

## 👤 Default Roles

| Role  | Access            |
| ----- | ----------------- |
| Admin | Full access       |
| Staff | Sales & inventory |

---

## 🧠 Business Logic Highlights

* **Stock never goes negative**
* **Bulk sell calculates sold automatically**
* **Inventory logs keep full history**
* **Profit = Revenue − Cost**
* **Each sale updates stock safely using transactions**

---

## 📌 Future Improvements (Optional)

* Per-user data separation
* Product categories
* Daily / monthly profit reports
* Barcode scanner support
* Mobile-friendly POS mode
* Email notifications for low stock

---

## 🧑‍💻 Author

**Ismail Hosen**
Foundation in Computing
Albukhary International University

---

## © Copyright

© 2025 **Ismail Hosen**
All rights reserved.

This project is developed for educational and business use.
Unauthorized commercial redistribution is not allowed.

---

## ❤️ Thank You

Thank you for using **DoorBite Food!**
If you like this project, feel free to improve or extend it 🚀

 

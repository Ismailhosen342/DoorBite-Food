# 🍟 DoorBite Food! 🍫  
### Snack & Food Inventory and Sales Management System

> **DoorBite Food!** is a modern, web-based inventory and sales management system designed for **small shops, cafeterias, and snack businesses**.  
>  
> Manage products, track stock, process sales (including bulk selling), analyze reports, and control user access — all from a clean and simple dashboard.

---

## 🌟 Why DoorBite Food?

✔ Simple to use  
✔ Real-time stock tracking  
✔ Accurate profit calculation  
✔ Perfect for small businesses & students  
✔ Clean UI with practical business logic  

---

## ✨ Core Features

### 🔐 Authentication & Access Control
- Secure **Login & Signup**
- **Role-based access** (Admin / Staff)
- PHP Session-based authentication
- Logout with session destruction

---

### 📊 Smart Dashboard
- 📅 Today’s Revenue
- 💰 Net Profit Calculation
- 📦 Total Products Overview
- ⚠️ Low Stock Alerts
- 🧾 Recent Sales Summary

---

### 🛒 Sales (POS System)
- Point-of-Sale style interface
- Add products to cart
- Automatic stock deduction
- Prevents selling out-of-stock items
- Transaction-safe sale processing

---

### 📦 Bulk Sell (Stock Count Method)
- Sell **multiple products at once**
- Enter **current counted stock**
- System auto-calculates sold quantity  
  *(Sold = Previous Stock − Current Stock)*
- Updates:
  - Products stock
  - Sales records
  - Inventory logs  
  all in **one action**

---

### 🧾 Product Management
- Add, edit & soft-delete products
- Buying price & selling price
- Live stock tracking
- Low-stock highlighting

---

### 📋 Inventory Logs
- Full stock movement history
- Reasons:
  - Restock
  - Adjustment
  - Sale
- Displays:
  - Product name
  - Old stock → New stock
- Helps prevent stock mismatch

---

### 📈 Reports & Analytics
- Total Revenue
- Total Cost
- Net Profit
- 📊 Last 7 Days Sales Chart
- 📦 Items Sold Statistics
- 📁 CSV Export support

---

### ⚙️ Settings
- User profile info
- Role display
- Extendable for future configurations

---

## 🧱 Technology Stack

| Layer | Technology |
|-----|-----------|
| Backend | PHP (PDO) |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Charts | Chart.js |
| Server | Apache / Nginx |
| Auth | PHP Sessions |

---

## 📁 Project Structure

```

/
├── config/
│   └── database.php       # Database connection & session helpers
├── includes/
│   ├── header.php         # HTML header template
│   ├── sidebar.php        # Navigation sidebar
│   └── footer.php         # Footer template
├── api/
│   ├── products.php       # Product CRUD API
│   ├── sales.php          # Sales processing API
│   ├── inventory.php      # Inventory & stock logs API
│   ├── settings.php       # Settings API
│   └── export.php         # CSV export
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   └── app.js             # JavaScript utilities
├── index.php              # Redirect to login
├── login.php              # Login page
├── logout.php             # Logout handler
├── dashboard.php          # Dashboard
├── sales.php              # POS sales page
├── bulk_sell.php          # Bulk selling page
├── products.php           # Products management
├── inventory.php          # Inventory logs
├── reports.php            # Reports & charts
└── settings.php           # App settings

````

---

## 🗄️ Database Tables

Main tables used in the system:

- `users`
- `products`
- `sales`
- `sale_items`
- `inventory_logs`

📌 **Every stock change is recorded** in `inventory_logs` for transparency.

---

## 🚀 Installation Guide

### 1️⃣ Clone or Upload Project
```bash
git clone https://github.com/your-username/doorbite-food.git
````

Or upload the files to your web server.

---

### 2️⃣ Create Database

```sql
CREATE DATABASE doorbite;
```

Import your SQL structure into this database.

---

### 3️⃣ Configure Database Connection

Edit:

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

### 4️⃣ Run the Application

Open in your browser:

```
http://localhost/doorbite/
```

---

## 👤 User Roles

| Role  | Permissions       |
| ----- | ----------------- |
| Admin | Full access       |
| Staff | Sales & inventory |

---

## 🧠 Business Logic Highlights

✔ Stock never goes negative
✔ Bulk sell auto-calculates sold quantity
✔ Inventory logs maintain full history
✔ Profit = Revenue − Cost
✔ Transactions ensure data safety

---

## 📌 Future Enhancements (Optional)

* Per-user data separation
* Product categories
* Monthly & yearly reports
* Barcode scanner integration
* Mobile-friendly POS
* Email alerts for low stock

---

## 🧑‍💻 Author

**Ismail Hosen**
Foundation in Computing
Albukhary International University

---

## © Copyright

© 2025 **Ismail Hosen**
All rights reserved.

This project is developed for **educational and small business use**.
Unauthorized commercial redistribution is prohibited.

---

## ❤️ Thank You

Thank you for using **DoorBite Food!**
If you like this project, feel free to ⭐ star it, improve it, or extend it 🚀

```

 

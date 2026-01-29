 

## 🗄️ Database Structure (MySQL)

This project uses **MySQL** as the database and follows a **shared inventory, role-based access** model suitable for small businesses and POS systems.

---

### 📌 users

Stores application users such as **Admin** and **Staff**.

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'Staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 📌 products

Stores product information and stock levels.

```sql
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  buying_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  selling_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  deleted_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 📌 sales

Stores completed sales transactions.

```sql
CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  total_amount DECIMAL(10,2) NOT NULL,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 📌 sale_items

Stores individual items sold in each sale.

```sql
CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);
```

---

### 📌 inventory_logs

Tracks stock changes such as restocks, sales, and adjustments.

```sql
CREATE TABLE inventory_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  reason VARCHAR(50) NOT NULL,
  change_amount INT NOT NULL,
  old_stock INT NOT NULL,
  new_stock INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id)
);
```

---

### 📌 settings

Stores system configuration values.

```sql
CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filter_type VARCHAR(20) NOT NULL DEFAULT 'daily',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔐 Access Control

* **Admin**: Full access (Products, Inventory, Reports, Settings)
* **Staff**: Sales and Dashboard access only
* Passwords are securely stored using `password_hash()` in PHP

---

## 🛠 Notes

* This database design supports a **single shared inventory**
* Ideal for **POS systems** with multiple staff accounts
* Soft delete is used for products (`is_deleted` flag)

---

### © All Rights Reserved

**DoorBite Food POS System**
© 2026 **Ismail Hosen**

---
 

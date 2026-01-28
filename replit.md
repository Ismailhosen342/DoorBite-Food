# DoorBite Food! - Food Shop Management System

## Overview
A complete food shop management system built with PHP, HTML, CSS, JavaScript, and PostgreSQL. The application allows managing products, processing sales, tracking inventory, and generating reports.

## Tech Stack
- **Backend**: PHP 8.2
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: PostgreSQL
- **Charts**: Chart.js

## Features
- **Login System**: Simple username-based login with role selection (Admin/Staff)
- **Dashboard**: View total revenue, net profit, product count, low stock alerts, and recent sales
- **Sales**: Point-of-sale interface with product cards and shopping cart
- **Products**: CRUD operations for product management
- **Inventory**: Track stock movements and restock items
- **Reports**: Sales analytics with charts and CSV export
- **Settings**: Dashboard filter preferences and trash management

## Project Structure
```
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
```

## Database Tables
- **users**: User accounts with roles
- **products**: Product catalog with pricing and stock
- **sales**: Sale transactions
- **sale_items**: Individual items in each sale
- **inventory_logs**: Stock movement history
- **settings**: Application preferences

## Running the Application
The application runs on PHP's built-in server on port 5000.

## Recent Changes
- January 28, 2026: Initial creation of DoorBite Food! system
  - Complete PHP application with all pages
  - PostgreSQL database integration
  - Responsive UI matching the design mockups

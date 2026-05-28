# Oli's SelfieTea & Coffee – Web System

## Setup Instructions

### 1. Requirements
- XAMPP (Apache + MySQL + PHP 8+)
- Visual Studio Code

### 2. Installation
1. Copy the `olis_coffee` folder to `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** in XAMPP Control Panel
3. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
4. Create database: Click **New** → name it `olis_coffee` → click Create
5. Click on `olis_coffee` database → go to **Import** tab
6. Choose the `database.sql` file → click **Go**
7. Open browser: `http://localhost/olis_coffee/login.php`

### 3. Demo Login Credentials
| Role     | Email                    | Password   |
|----------|--------------------------|------------|
| Admin    | admin@oliscoffee.com     | password   |
| Customer | customer@email.com       | password   |

> **Note:** The password hash in the SQL file uses `password` as the plain text password.

---

## File Structure
```
olis_coffee/
├── login.php                  ← Login page (OOP Auth)
├── logout.php                 ← Logout (destroys session)
├── index.php                  ← Customer: Main Menu (with dish photos)
├── menu.php                   ← Full menu page (Snacks, Pasta, Pizza, Drinks…)
├── book_reservation.php       ← Seat reservation
├── chatbot.php                ← AI chatbot (Ask Oli)
├── profile.php                ← Edit profile / change password
├── about.php                  ← About the project
├── contact.php                ← Contact info
├── database.sql               ← MySQL schema + seed data (all categories incl. Drinks)
├── css/
│   └── style.css              ← Custom styles + Bootstrap override
├── js/
│   └── script.js              ← Tab filtering, animations, JS logic
├── uploads/
│   └── menu/                  ← Dish photos uploaded via admin (auto-created)
├── includes/
│   ├── db.php                 ← Database connection (Singleton OOP)
│   ├── Auth.php               ← Authentication class (OOP)
│   └── MenuItem.php           ← Menu item CRUD class with image support
└── admin/
    ├── dashboard.php          ← Admin dashboard + stats + category breakdown
    ├── menu_manage.php        ← CRUD with category tabs + photo upload
    ├── manage_reservations.php← Approve/cancel reservations
    └── reports.php            ← JOIN query reports
```

## What's New / Changed

### Admin Menu Management (`admin/menu_manage.php`)
- Items are now organized by **category tabs**: Main | Snacks | Pasta | Burgers | Salads | Pizza | Drinks
- Within each category, items are grouped by **subcategory** with section headers
- **Drinks category** is fully visible in admin (previously missing)
- Subcategory dropdown updates dynamically when you change the category

### Dish Photo Upload
- Admin can now upload a photo for each menu item (except Drinks)
- Supported formats: JPG, PNG, WebP, GIF
- Photos are stored in `uploads/menu/`
- Customer-facing menu cards show the photo if uploaded, or a "No photo yet" placeholder
- Photos can be replaced or removed in the edit form

### Database (`database.sql`)
- Added `image VARCHAR(255)` column to `menu_items`
- Full seed data now includes: Snacks, Pasta, Burgers, Salads, Pizza, and all Drinks subcategories
- Total seeded items: 100+

## Features Demonstrated
- ✅ OOP PHP (classes: Database, Auth, MenuItem)
- ✅ Session-based login/logout
- ✅ Password hashing (password_hash / password_verify)
- ✅ MySQL relational database (4+ tables)
- ✅ Foreign Keys & One-to-Many relationships
- ✅ CRUD operations with image upload
- ✅ JOIN queries (4-table JOIN in reports)
- ✅ Bootstrap 5 UI
- ✅ Custom CSS + animations
- ✅ JavaScript (tab filtering, dynamic dropdowns, image preview)
- ✅ AI Chatbot (Claude API)
- ✅ GCash reservation fee

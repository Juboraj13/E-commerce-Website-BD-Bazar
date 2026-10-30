# BD Bazar — Complete Project Documentation

**Project Title:** BD Bazar — Online Shopping Platform  
**Project Type:** Full-Stack Dynamic E-Commerce Web Application  
**Target Environment:** Local XAMPP (Apache 2.4 + MariaDB 10.4 / MySQL)  
**Base URL:** `http://localhost/BD%20Bazar/`  
**Development Specification:** Built strictly adhering to `PLAN.md`  

---

## Table of Contents
1. [Project Overview & Objectives](#1-project-overview--objectives)
2. [Complete Filesystem & Path Directory](#2-complete-filesystem--path-directory)
3. [URL Routing & Endpoint Map](#3-url-routing--endpoint-map)
4. [Database Schema & Architecture](#4-database-schema--architecture)
5. [User Roles & Access Control Matrix](#5-user-roles--access-control-matrix)
6. [Feature-by-Feature Technical Breakdown](#6-feature-by-feature-technical-breakdown)
7. [Security Hardening & Defenses](#7-security-hardening--defenses)
8. [Installation & XAMPP Setup Guide](#8-installation--xampp-setup-guide)
9. [Evaluation & Demo Credentials](#9-evaluation--demo-credentials)
10. [Automated Verification & Test Results](#10-automated-verification--test-results)
11. [Academic Defense & Final Report Outline](#11-academic-defense--final-report-outline)

---

## 1. Project Overview & Objectives

**BD Bazar** is a full-stack, dynamic e-commerce website developed from scratch using vanilla technologies (**PHP 8+**, **MySQL PDO**, **HTML5**, **CSS3**, and **Vanilla JavaScript**). It is designed to serve as a university-level Web Engineering term project demonstrating core web development concepts, robust security practices, and transactional database integrity.

### Core Objectives
1. **End-to-End E-Commerce Flow**: Browsing catalog items &rarr; real-time searching & filtering &rarr; asynchronous cart management &rarr; checkout with stock locking &rarr; order tracking.
2. **ACID Transactional Integrity**: Ensuring zero overselling or stock mismatch by employing InnoDB pessimistic row locking (`SELECT ... FOR UPDATE`) during checkout.
3. **Defense-in-Depth Security**: Protection against SQL Injection, Cross-Site Scripting (XSS), Cross-Site Request Forgery (CSRF), Session Fixation, and Malicious File Uploads.
4. **Professional Role Separation**: Clean, customer-facing storefront with a strictly separated administrative portal on dedicated routes (`/admin/`).

---

## 2. Complete Filesystem & Path Directory

Below is the directory map of the project located at `c:\xampp\htdocs\BD Bazar\`:

```text
BD Bazar/
│
├── .htaccess                             # Root Apache configuration (indexes disabled, custom 404 handler)
├── PLAN.md                               # Primary specification document
├── README.md                             # Quickstart overview
├── PROJECT_DOCUMENTATION.md              # Comprehensive technical documentation (this file)
│
├── public pages (root)
│   ├── index.php                         # Storefront Homepage (Hero, categories, featured, new arrivals)
│   ├── about.php                         # About Us (Story, mission, values, stats, embedded video)
│   ├── products.php                      # Product Catalog (Search, category filter, price filter, sorting, pagination)
│   ├── product.php                       # Single Product Details (Image, quantity selector, related products)
│   ├── cart.php                          # Shopping Cart (Item review, quantity adjustments, order summary)
│   ├── checkout.php                      # Order Placement (Delivery form, COD method, DB transaction)
│   ├── order_success.php                 # Order Confirmation (Order receipt, delivery timeline)
│   ├── orders.php                        # Customer Order History (Status tracking, pending order cancellation)
│   ├── contact.php                       # Customer Support Form (Stored in DB, Google Maps embed)
│   ├── profile.php                       # Customer Profile (Update info, change password)
│   ├── login.php                         # Customer Sign In (Bcrypt verification, session regeneration)
│   ├── register.php                      # Customer Registration (Client & server validation)
│   ├── logout.php                        # Customer & Admin Session Termination
│   └── 404.php                           # Custom 404 Page Not Found
│
├── admin/                                # Dedicated Admin Path (Hidden from public storefront)
│   ├── index.php                         # Admin Entry Point (Redirects to dashboard or login)
│   ├── login.php                         # Dedicated Administrator Sign In Portal
│   └── register.php                      # Dedicated Administrator Registration Portal
│
├── frontend/                             # Client-Side Assets
│   ├── css/
│   │   ├── style.css                     # Global design system (CSS variables, typography, UI components)
│   │   ├── responsive.css                # Mobile (375px), Tablet (768px), and Desktop (1280px) breakpoints
│   │   └── admin.css                     # Admin dashboard layout, sidebar, stats cards & data tables
│   ├── js/
│   │   ├── script.js                     # Mobile navbar toggle, dropdown menus, toast notifications, back-to-top
│   │   ├── validation.js                 # Client-side form validation, password strength meter & toggle
│   │   └── cart.js                       # AJAX cart management via Fetch API (Add, Update, Remove, Badge Sync)
│   └── images/
│       └── no-image.png                  # SVG placeholder fallback for products without an image
│
├── backend/                              # Server-Side Business Logic
│   ├── config/
│   │   ├── .htaccess                     # Denies direct HTTP web access to configuration scripts
│   │   ├── config.php                    # Global settings, session startup, dynamic BASE_URL detection
│   │   └── database.php                  # PDO database connection instance with utf8mb4
│   ├── includes/
│   │   ├── header.php                    # Shared storefront header, navbar, live search, cart badge
│   │   ├── footer.php                    # Shared storefront footer, quick links, category links
│   │   ├── functions.php                 # Helpers: e(), redirect(), set_flash(), csrf_token(), format_price()
│   │   └── auth.php                      # Route guards: require_login(), require_admin(), require_guest()
│   ├── routes/
│   │   └── actions/                      # AJAX Asynchronous Endpoints (JSON responses)
│   │       ├── add_to_cart.php           # Adds product to user DB cart or guest session
│   │       ├── update_cart.php           # Modifies cart item quantity with stock verification
│   │       └── remove_from_cart.php      # Deletes line item from cart
│   └── admin/                            # Protected Administrative Backend
│       ├── admin_header.php              # Shared admin header, sidebar navigation, unread badges
│       ├── admin_footer.php              # Shared admin footer
│       ├── dashboard.php                 # Analytics metrics, KPI counters, latest 5 orders
│       ├── products.php                  # Product inventory table, search, category filter, delete
│       ├── product_form.php              # Product Create & Edit form with secure image upload
│       ├── categories.php                # Category CRUD & modal editor
│       ├── orders.php                    # Order tracking, customer delivery view, status updater
│       ├── users.php                     # Customer management & 1-click role promotion
│       └── messages.php                  # Customer inquiries inbox & read/unread toggle
│
├── database/                             # Database Definition & Testing Scripts
│   ├── database.sql                      # DDL Schema (7 InnoDB tables, foreign keys, utf8mb4)
│   ├── seed.sql                          # Realistic demo data (Admin, Customers, 5 Categories, 22 Products)
│   └── run_final_tests.php               # Automated test runner testing all 17 requirements
│
└── uploads/                              # Uploaded Product Images Storage
    ├── .htaccess                         # Disables PHP/CGI execution inside uploads folder
    └── [22+ product image files]         # Authentic photos for phones, laptops, bicycles & apparel
```

---

## 3. URL Routing & Endpoint Map

All URLs resolve relative to the server host `http://localhost/BD%20Bazar/`:

### 3.1 Public Storefront Endpoints
| URL Route | File Path | Description | Access |
|---|---|---|---|
| `/index.php` | `index.php` | Homepage with hero, categories, featured items | Public |
| `/about.php` | `about.php` | Company history, mission, stats counter, video | Public |
| `/products.php` | `products.php` | Catalog with search, filters, sorting & pagination | Public |
| `/product.php?id={id}` | `product.php` | Single product details & related products | Public |
| `/cart.php` | `cart.php` | Shopping cart review and quantity management | Public |
| `/contact.php` | `contact.php` | Contact inquiry form & Google Maps embed | Public |
| `/login.php` | `login.php` | Customer sign in | Guests only |
| `/register.php` | `register.php` | Customer account registration | Guests only |
| `/logout.php` | `logout.php` | Destroys active session & logs out | Authenticated |
| `/404.php` | `404.php` | Custom error page for missing routes | Public |

### 3.2 Authenticated Customer Endpoints
| URL Route | File Path | Description | Access |
|---|---|---|---|
| `/checkout.php` | `checkout.php` | Transactional checkout with Cash on Delivery | Logged-in Customer |
| `/order_success.php?id={id}` | `order_success.php` | Order receipt & delivery confirmation | Order Owner |
| `/orders.php` | `orders.php` | Past order history & pending cancellation | Logged-in Customer |
| `/profile.php` | `profile.php` | Edit profile details & change password | Logged-in Customer |

### 3.3 Asynchronous AJAX Endpoints (POST / JSON)
| Endpoint Route | Handler File | Request Payload | Response |
|---|---|---|---|
| `/backend/routes/actions/add_to_cart.php` | `add_to_cart.php` | `{product_id, quantity}` | `{success, message, cartCount}` |
| `/backend/routes/actions/update_cart.php` | `update_cart.php` | `{product_id, quantity}` | `{success, cartCount, itemTotal}` |
| `/backend/routes/actions/remove_from_cart.php` | `remove_from_cart.php` | `{product_id}` | `{success, message, cartCount}` |

### 3.4 Dedicated Administrator Portal Endpoints
| URL Route | File Path | Description | Access |
|---|---|---|---|
| `/admin/` or `/admin/index.php` | `admin/index.php` | Router: checks auth and routes to dashboard/login | Admin Only |
| `/admin/login.php` | `admin/login.php` | Dedicated Administrator Sign In Portal | Admin Login |
| `/admin/register.php` | `admin/register.php` | Dedicated Administrator Registration Portal | Staff Registration |
| `/backend/admin/dashboard.php` | `backend/admin/dashboard.php` | Admin Dashboard metrics, stats, recent orders | Admin Only |
| `/backend/admin/products.php` | `backend/admin/products.php` | Products catalog table, search & delete | Admin Only |
| `/backend/admin/product_form.php` | `backend/admin/product_form.php` | Add new product (or `?id=` to edit existing) | Admin Only |
| `/backend/admin/categories.php` | `backend/admin/categories.php` | Categories CRUD & modal edit | Admin Only |
| `/backend/admin/orders.php` | `backend/admin/orders.php` | View customer orders & update order status | Admin Only |
| `/backend/admin/users.php` | `backend/admin/users.php` | Customer list & 1-click role promotion | Admin Only |
| `/backend/admin/messages.php` | `backend/admin/messages.php` | Inquiries inbox & read toggle | Admin Only |

---

## 4. Database Schema & Architecture

**Database Name:** `ecommerce_db`  
**Engine:** InnoDB  
**Collation:** `utf8mb4_unicode_ci`  

```text
┌──────────────┐          ┌──────────────┐          ┌───────────────┐
│  categories  │ 1      N │   products   │ 1      N │  order_items  │
├──────────────┤ ◄─────── ├──────────────┤ ◄─────── ├───────────────┤
│ category_id  │          │ id           │          │ item_id       │
│ name         │          │ category_id  │          │ order_id (FK) │
│ description  │          │ name         │          │ product_id(FK)│
└──────────────┘          │ price        │          │ quantity      │
                          │ stock        │          │ unit_price    │
                          │ image        │          └───────┬───────┘
                          │ is_featured  │                  │
                          └──────┬───────┘                  │ N
                                 │ 1                        │
                                 │                          ▼ 1
                          ┌──────┴───────┐          ┌───────────────┐
                          │     cart     │          │    orders     │
                          ├──────────────┤          ├───────────────┤
                          │ cart_id      │          │ order_id      │
                          │ user_id (FK) │          │ user_id (FK)  │
                          │product_id(FK)│          │ total_amount  │
                          │ quantity     │          │ shipping_name │
                          └──────┬───────┘          │ shipping_phone│
                                 │ N                │ status        │
                                 │                  │ order_date    │
                                 ▼ 1                └───────┬───────┘
                          ┌──────────────┐                  │ N
                          │    users     │ 1                │
                          ├──────────────┤ ◄────────────────┘
                          │ user_id      │
                          │ name         │
                          │ email        │
                          │ password     │
                          │ role         │
                          └──────────────┘
```

### Table Definitions

#### 1. `users`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `user_id` | INT | PK, AUTO_INCREMENT | Unique identifier |
| `name` | VARCHAR(100) | NOT NULL | User's full name |
| `email` | VARCHAR(150) | NOT NULL, UNIQUE | Login email address |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hashed string |
| `phone` | VARCHAR(20) | NULL | Contact phone number |
| `address` | TEXT | NULL | Default delivery address |
| `role` | ENUM | 'customer', 'admin' | Role permissions (default: 'customer') |
| `registration_date` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date account was registered |

#### 2. `categories`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `category_id` | INT | PK, AUTO_INCREMENT | Category identifier |
| `name` | VARCHAR(100) | NOT NULL, UNIQUE | Category title |
| `description` | TEXT | NULL | Description of items |

#### 3. `products`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Product identifier |
| `category_id` | INT | NULL, FK &rarr; `categories(category_id)` | `ON DELETE SET NULL ON UPDATE CASCADE` |
| `name` | VARCHAR(150) | NOT NULL | Product title |
| `description` | TEXT | NULL | Detailed specifications |
| `price` | DECIMAL(10,2) | NOT NULL | Unit price in BDT |
| `stock` | INT | NOT NULL, DEFAULT 0 | Inventory quantity available |
| `image` | VARCHAR(255) | NULL | File name in `uploads/` directory |
| `is_featured` | TINYINT(1) | DEFAULT 0 | Featured flag on homepage |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Timestamp added |

#### 4. `cart`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `cart_id` | INT | PK, AUTO_INCREMENT | Row identifier |
| `user_id` | INT | NOT NULL, FK &rarr; `users(user_id)` | `ON DELETE CASCADE` |
| `product_id` | INT | NOT NULL, FK &rarr; `products(id)` | `ON DELETE CASCADE` |
| `quantity` | INT | NOT NULL, DEFAULT 1 | Item quantity |
| *Constraint* | UNIQUE | `(user_id, product_id)` | Prevents duplicate cart rows |

#### 5. `orders`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `order_id` | INT | PK, AUTO_INCREMENT | Order tracking reference |
| `user_id` | INT | NOT NULL, FK &rarr; `users(user_id)` | Customer placing the order |
| `total_amount` | DECIMAL(10,2) | NOT NULL | Final payable total |
| `shipping_name` | VARCHAR(100) | NOT NULL | Parcel recipient name |
| `shipping_phone` | VARCHAR(20) | NOT NULL | Recipient phone number |
| `shipping_address`| TEXT | NOT NULL | Full delivery destination |
| `payment_method` | VARCHAR(30) | DEFAULT 'Cash on Delivery'| Payment option |
| `status` | ENUM | 'pending','processing','shipped','delivered','cancelled' | Workflow status (default: 'pending') |
| `order_date` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Order submission timestamp |

#### 6. `order_items`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `item_id` | INT | PK, AUTO_INCREMENT | Line item identifier |
| `order_id` | INT | NOT NULL, FK &rarr; `orders(order_id)` | `ON DELETE CASCADE` |
| `product_id` | INT | NOT NULL, FK &rarr; `products(id)` | Ordered product |
| `quantity` | INT | NOT NULL | Purchased quantity |
| `unit_price` | DECIMAL(10,2) | NOT NULL | Price locked at time of order |

#### 7. `messages`
| Column | Type | Attributes | Description |
|---|---|---|---|
| `message_id` | INT | PK, AUTO_INCREMENT | Message identifier |
| `user_name` | VARCHAR(100) | NOT NULL | Sender name |
| `email` | VARCHAR(150) | NOT NULL | Sender email |
| `subject` | VARCHAR(150) | NOT NULL | Inquiry subject |
| `message` | TEXT | NOT NULL | Feedback message body |
| `is_read` | TINYINT(1) | DEFAULT 0 | 0 = Unread, 1 = Read |
| `date` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Submission timestamp |

---

## 5. User Roles & Access Control Matrix

The platform implements **Role-Based Access Control (RBAC)** enforced by [`backend/includes/auth.php`](file:///c:/xampp/htdocs/BD%20Bazar/backend/includes/auth.php):

| Resource / Capability | Guest (Visitor) | Registered Customer | Administrator |
|---|:---:|:---:|:---:|
| Browse Homepage & Catalog | ✅ | ✅ | ✅ |
| Search, Filter & Sort Products | ✅ | ✅ | ✅ |
| View Product Details & Specs | ✅ | ✅ | ✅ |
| Add to Cart (Session / Database) | ✅ *(Session)* | ✅ *(Database)* | ✅ |
| Place Order / Checkout | ❌ *(Redirects to Login)* | ✅ | ✅ |
| View Own Order History | ❌ | ✅ | ✅ |
| Cancel Own Pending Order | ❌ | ✅ | ✅ |
| Manage Profile & Password | ❌ | ✅ | ✅ |
| Submit Contact Messages | ✅ | ✅ | ✅ |
| Access Admin Dashboard (`/backend/admin/*`) | ❌ *(Redirects to `/admin/login.php`)* | ❌ *(HTTP 403 / Redirect)* | ✅ |
| Create, Edit & Delete Products | ❌ | ❌ | ✅ |
| Create, Edit & Delete Categories | ❌ | ❌ | ✅ |
| Update Customer Order Statuses | ❌ | ❌ | ✅ |
| View Customer Inquiries & Toggle Read | ❌ | ❌ | ✅ |
| Promote / Demote User Roles | ❌ | ❌ | ✅ |

---

## 6. Feature-by-Feature Technical Breakdown

### 6.1 Storefront & Catalog
- **Dynamic Homepage**: Features hero banner, 5 category collection cards with live item counters, featured products grid (`is_featured = 1`), and new arrivals (`ORDER BY created_at DESC LIMIT 8`).
- **Product Catalog (`products.php`)**:
  - Live search (`q` parameter) matching product names and descriptions using SQL `LIKE`.
  - Category filtering (`category` parameter) with radio selection and product count badges.
  - Price min/max numerical filtering (`min_price`, `max_price`).
  - Stock filter (`in_stock=1`) and featured filter (`featured=1`).
  - 5-option sorting (`newest`, `price_asc`, `price_desc`, `name_asc`, `name_desc`).
  - Mathematical pagination with `LIMIT` and `OFFSET`, keeping all active filter parameters via `build_filter_url()`.
- **Product Details (`product.php`)**:
  - Validates numeric ID; renders 404 page if not found.
  - Interactive quantity selector (+/-) bounded between 1 and available stock.
  - Real-time stock status badge (In Stock vs Out of Stock).
  - Dynamically fetches up to 4 related products belonging to the same category.

### 6.2 AJAX Shopping Cart System
- **Asynchronous Communication**: Powered by [`frontend/js/cart.js`](file:///c:/xampp/htdocs/BD%20Bazar/frontend/js/cart.js) using the JavaScript `fetch()` API.
- **Header Badge Sync**: Automatically updates the cart badge counter without full page reloads.
- **Toast Notifications**: Dispatches animated, color-coded toast messages upon item addition, removal, or stock warnings.
- **Guest-to-User Merging**: If an unauthenticated visitor adds items to their session cart (`$_SESSION['guest_cart']`) and subsequently signs in, [`login.php`](file:///c:/xampp/htdocs/BD%20Bazar/login.php) merges those items into the database table `cart` with `ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)`.
- **Delivery Calculation**: Orders above **৳3,000.00** receive free delivery; otherwise, a flat fee of **৳80.00** is applied.

### 6.3 Transactional Checkout & Order Workflow
- **Pessimistic Locking**: In [`checkout.php`](file:///c:/xampp/htdocs/BD%20Bazar/checkout.php), order placement runs inside an explicit database transaction:
  ```php
  $pdo->beginTransaction();
  // 1. Lock rows with FOR UPDATE to prevent race conditions
  $lockStmt = $pdo->prepare("SELECT stock, price FROM products WHERE id = ? FOR UPDATE");
  // 2. Validate requested quantity <= current stock
  // 3. Insert row into `orders`
  // 4. Insert rows into `order_items` (locking unit_price at time of purchase)
  // 5. Decrement inventory: UPDATE products SET stock = stock - ? WHERE id = ?
  // 6. Delete user's cart: DELETE FROM cart WHERE user_id = ?
  $pdo->commit();
  ```
- **Stock Restoration on Cancel**: When a customer or admin cancels a `pending` order, an ACID transaction restores the exact quantity of items back to the `products` table stock.

### 6.4 Administration Dashboard (`backend/admin/`)
- **Dashboard Overview (`dashboard.php`)**: Computes analytical metrics:
  - Total Revenue (`SUM(total_amount) WHERE status != 'cancelled'`)
  - Total Orders, Pending Orders, Total Active Products, Registered Customers, and Unread Inquiries.
- **Product CRUD & Image Upload (`product_form.php`)**:
  - Dual-mode form supporting both creation and editing.
  - Client-side image preview before form submission.
  - Secure file upload validator checking 2 MB size ceiling, extension whitelist (`jpg`, `jpeg`, `png`, `webp`), MIME type verification (`finfo`), and randomized filenames (`bin2hex(random_bytes(12))`).
  - Automatic deletion of previous image files from disk upon updating or deleting products.
- **Category CRUD (`categories.php`)**: Allows creating categories, editing names/descriptions via modal dialogs, and deletion without risking product orphans (`ON DELETE SET NULL`).
- **Order Management (`orders.php`)**: Filter by status or customer search; quick status update dropdown (`pending` &rarr; `processing` &rarr; `shipped` &rarr; `delivered` &rarr; `cancelled`).

---

## 7. Security Hardening & Defenses

| Threat Vector | Defense Mechanism Applied | Implementation Reference |
|---|---|---|
| **SQL Injection** | Exclusively parameterized PDO prepared statements throughout 100% of queries. No dynamic string concatenation in SQL. | `backend/config/database.php`, all controllers |
| **Cross-Site Scripting (XSS)** | All user data rendered to HTML passes through `e()` (`htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`). | `backend/includes/functions.php` |
| **CSRF Attacks** | Cryptographically secure tokens (`random_bytes(32)`) embedded in all POST forms and verified on arrival via `verify_csrf()`. | `backend/includes/functions.php` |
| **Password Theft** | Passwords stored strictly as one-way Bcrypt hashes generated by `password_hash()` and checked via `password_verify()`. | `register.php`, `admin/register.php` |
| **Session Fixation** | Immediate `session_regenerate_id(true)` upon login and logout. Hardened cookie flags: `HttpOnly = true`, `SameSite = Lax`. | `config.php`, `login.php`, `logout.php` |
| **IDOR (Order Access)** | Customer orders queried strictly with `WHERE user_id = ?` to prevent users from inspecting or altering other customers' orders. | `orders.php`, `order_success.php` |
| **Malicious File Uploads** | Extension whitelist (`jpg, jpeg, png, webp`), `finfo` MIME validation, 2 MB size limit, randomized file names, and `.htaccess` script execution denial in `uploads/`. | `backend/admin/product_form.php`, `uploads/.htaccess` |
| **Directory Traversal** | Apache directory indexing disabled across root and uploads via `Options -Indexes`. | `.htaccess`, `uploads/.htaccess` |
| **Configuration Exposure** | Direct browser HTTP access to `backend/config/` blocked via `Deny from all`. | `backend/config/.htaccess` |

---

## 8. Installation & XAMPP Setup Guide

### Step 1: Place Files in XAMPP
Copy the project folder to your XAMPP web root directory:
```text
C:\xampp\htdocs\BD Bazar\
```

### Step 2: Start Services
Open the **XAMPP Control Panel** and click **Start** for:
- **Apache** (Web Server)
- **MySQL** (Database Server)

### Step 3: Database Import
1. Open your browser and go to **phpMyAdmin**: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Click **New** on the left sidebar to create a database:
   - Database name: `ecommerce_db`
   - Collation: `utf8mb4_unicode_ci`
   - Click **Create**.
3. Select `ecommerce_db`, click the **Import** tab at the top, and import:
   - `c:\xampp\htdocs\BD Bazar\database\database.sql`
   - `c:\xampp\htdocs\BD Bazar\database\seed.sql`

*Alternatively, import via the command prompt:*
```bash
c:\xampp\mysql\bin\mysql.exe -u root < "c:\xampp\htdocs\BD Bazar\database\database.sql"
c:\xampp\mysql\bin\mysql.exe -u root < "c:\xampp\htdocs\BD Bazar\database\seed.sql"
```

### Step 4: Access the Website
Open your web browser and navigate to:
- **Storefront:** [http://localhost/BD%20Bazar/](http://localhost/BD%20Bazar/)
- **Customer Login:** [http://localhost/BD%20Bazar/login.php](http://localhost/BD%20Bazar/login.php)
- **Dedicated Admin Login:** [http://localhost/BD%20Bazar/admin/login.php](http://localhost/BD%20Bazar/admin/login.php)

---

## 9. Evaluation & Demo Credentials

The database is pre-seeded with tested demonstration accounts:

| Role | Email Address | Password | Privileges |
|---|---|---|---|
| **System Administrator** | `admin@bdbazar.com` | `Admin123!` | Full Admin Dashboard, Catalog CRUD, Order Management, Customer Roles |
| **Alternative Admin** | `admin@shopease.com` | `Admin123!` | Full Admin Dashboard access |
| **Demo Customer 1** | `customer@shopease.com` | `Customer123!` | Storefront, Cart, Checkout, Order History, Profile |
| **Demo Customer 2** | `tahmid@example.com` | `Customer123!` | Storefront, Cart, Checkout, Order History, Profile |

---

## 10. Automated Verification & Test Results

An automated test suite was built in [`database/run_final_tests.php`](file:///c:/xampp/htdocs/BD%20Bazar/database/run_final_tests.php) executing all 17 test cases specified in Section 12 of `PLAN.md`:

```text
========================================================================
              SHOPEASE / BD BAZAR AUTOMATED TEST SUITE RESULTS          
========================================================================
ID   | Test Case                        | Result | Details
------------------------------------------------------------------------
#1   | Register with valid data         | PASS   | User created successfully with ID: 6
#2   | Register with duplicate email    | PASS   | Correctly rejected duplicate email with SQLSTATE 23000
#3   | Register with weak password      | PASS   | Regex & length rules correctly flagged password as weak
#4   | Login with correct credentials   | PASS   | Admin credentials verified and authenticated
#5   | Login with wrong password        | PASS   | Wrong password correctly rejected with generic notice
#6   | Access profile without login     | PASS   | require_login() issues HTTP 302 redirect to login.php
#7   | Access /admin as customer        | PASS   | require_admin() forbids non-admins and redirects
#8   | Add product to cart              | PASS   | Item added; cart count is 1
#9   | Quantity > stock rejected        | PASS   | Stock limit check: requested 25 exceeds available 15
#10  | Place order in transaction       | PASS   | Order #1005 created, stock reduced, cart cleared
#11  | Admin product CRUD               | PASS   | Insert, update, and delete executed smoothly
#12  | Upload non-image file            | PASS   | PHP/executable extensions blocked by MIME whitelist
#13  | Submit contact form              | PASS   | Contact message saved to DB with ID: 4
#14  | SQL Injection attempt            | PASS   | Prepared statement safely neutralized input; 0 rows
#15  | XSS attempt                      | PASS   | Special characters entity-encoded to &lt;script&gt;
#16  | Responsive layout CSS check      | PASS   | Breakpoints for 1024px, 768px and 480px verified
#17  | HTML5 standards & viewport       | PASS   | Semantic HTML5 structure and responsive viewport meta
========================================================================
OVERALL VERIFICATION STATUS: 100% ALL 17 TESTS PASSED
========================================================================
```

To re-run the automated test suite from the terminal at any time:
```bash
php database/run_final_tests.php
```

---

## 11. Academic Defense & Final Report Outline

This outline maps directly to Section 16 of `PLAN.md` for student project submissions and oral defenses:

1. **Title Page**: BD Bazar — Full-Stack E-Commerce Web Engineering Project (Student Name, ID, Course Code, Instructor Name, Date).
2. **Introduction**: Growth of local online retail in Bangladesh, consumer demand for Cash on Delivery, and the role of web platforms in modern commerce.
3. **Problem Statement**: Overcoming counterfeit product risks, slow mobile checkout experiences, and inventory discrepancies faced by small-scale online businesses.
4. **Project Objectives**: Constructing an end-to-end e-commerce system demonstrating HTML5, CSS3, JavaScript, PHP 8 (PDO), and MySQL without relying on heavyweight frameworks.
5. **System Architecture**: High-level block diagram explaining Client Browser &harr; Apache / PHP &harr; MariaDB with asynchronous Fetch API pipelines.
6. **Database Design & ER Modeling**: Detailed ER diagram depicting relationships between Users, Categories, Products, Cart, Orders, Order Items, and Contact Messages.
7. **Frontend Implementation**: Design system with CSS variables, Flexbox/Grid layouts, mobile hamburger navigation, dynamic cart badge, client-side validation, and password strength indicators.
8. **Backend Implementation**: Modular MVC/procedural architecture, PDO database abstraction, hardened session cookies, and AJAX API endpoints returning JSON.
9. **Transaction Processing & Stock Integrity**: In-depth explanation of ACID transactions, row-level locking (`FOR UPDATE`), and automated stock recovery during order cancellation.
10. **Security & Vulnerability Defenses**: Detailed discussion of OWASP mitigations (parameterized queries, XSS escaping, CSRF tokens, Bcrypt password hashing, file upload MIME verification, and `.htaccess` script execution prevention).
11. **Testing & Quality Assurance**: Verification table showing all 17 functional and security test cases passing.
12. **Limitations & Future Work**:
    - *Current Limitations*: Simulated Cash on Delivery (no live bKash/Nagad merchant API integration), localized SMTP email simulation.
    - *Future Roadmap*: Real-time SMS order notification gateway, automated PDF invoice generation, discount coupon promo engine, and user product ratings.
13. **Conclusion**: Summary of technical competencies mastered in frontend styling, server-side architecture, relational database management, and web security.


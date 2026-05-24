# YST Digital — Premium Digital Product Store

A fully responsive digital product selling platform with a native mobile app experience and a professional desktop website. Built with **PHP + MDBootstrap + MySQL**.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white)
![MDBootstrap](https://img.shields.io/badge/MDBootstrap-6.4-EA4C89?style=flat)
![License](https://img.shields.io/badge/License-MIT-green?style=flat)

---

## ✨ Features

### 🛒 User-Facing Storefront
- **Landing Page** — Hero section, Featured Products, Testimonials, FAQ
- **Product Catalog** — Grid (desktop) / vertical list (mobile), Search + Category + Price filters
- **Product Detail** — Screenshot carousel, description, Add to Cart / Buy Now
- **Shopping Cart** — Client-side localStorage + server session sync
- **Checkout** — Coupon/discount codes, Stripe / PayPal / Razorpay gateway simulation
- **Order History** — Accordion view with secure download buttons (HMAC token-expiry)
- **Printable Invoices** — Auto-generated PDF-style receipt per order
- **User Profile** — Edit name, email, password
- **Support & FAQs** — Ticket submission form + dynamic FAQ accordions

### 🔐 Admin Dashboard
- **Overview** — Revenue, tax collected, active products, open support tickets
- **Product Management** — Add / Edit / Delete with status toggle (Active/Inactive)
- **Order Management** — Full transaction log, refund processing
- **User Management** — Block / Unblock users, view purchase history per customer
- **Coupons & Discounts** — Flat or percentage codes with expiry date + usage limits
- **Support Inbox** — Reply to tickets, update status, manage FAQ articles
- **Settings** — Payment gateway API keys, tax/VAT rate, currency, branding

### 🎨 Design
- **Dark + Light mode** (persisted via localStorage, flash-free)
- **Mobile** — Material Design-inspired AppBar + Bottom Navigation (Home/Products/Cart/Profile)
- **Desktop** — Glassmorphic top navbar, professional eCommerce layout
- **Glassmorphism** cards, smooth animations, gradient accents
- Responsive across mobile, tablet, and desktop

---

## 🚀 Quick Setup (XAMPP)

### 1. Clone the repository
```bash
git clone https://github.com/YOUR_USERNAME/yst-digital.git
cd yst-digital
```

### 2. Set up the database
Open **phpMyAdmin** → Import `database.sql`
```
Database name: yst_digital_store
```

### 3. Configure the application
```bash
cp config.example.php config.php
```
Edit `config.php` and fill in your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'yst_digital_store');
```

### 4. Move to XAMPP htdocs
Place the project folder inside `C:/xampp/htdocs/` and access it at:
```
http://localhost/yst-digital/
```

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@ystdigital.com` | `admin123` |
| Editor | `editor@ystdigital.com` | `admin123` |
| Demo Customer | `user@ystdigital.com` | `user123` |

> **Note:** Change all passwords after your first login in a production environment.

---

## 🗂️ Project Structure

```
yst-digital/
├── index.php               # Landing page (Hero, Products, Testimonials, FAQ)
├── products.php            # Product listing with filters
├── product-detail.php      # Individual product view + carousel
├── cart.php                # Shopping cart
├── checkout.php            # Payment gateway simulation
├── orders.php              # Order history + secure downloads
├── download.php            # HMAC-validated secure file downloader + invoices
├── login.php               # Authentication
├── signup.php              # User registration
├── profile.php             # Profile settings
├── forgot-password.php     # Password recovery
├── reset-password.php      # Password reset
├── contact.php             # Support tickets + FAQs
├── logout.php              # Session destroy
├── config.php              # ⚠️ NOT in Git — copy from config.example.php
├── config.example.php      # Config template (safe for Git)
├── database.sql            # Full schema + seed data
│
├── includes/
│   ├── header.php          # Global HTML head + desktop navbar + mobile AppBar
│   ├── footer.php          # Global footer + mobile bottom nav
│   ├── navbar.php          # Desktop top navbar component
│   └── bottom-nav.php      # Mobile bottom navigation bar
│
├── admin/
│   ├── index.php           # Dashboard analytics
│   ├── products.php        # Product CRUD
│   ├── orders.php          # Orders + refunds
│   ├── users.php           # User management
│   ├── coupons.php         # Coupon management
│   ├── support.php         # Ticket inbox + FAQ editor
│   ├── settings.php        # System settings (Super Admin only)
│   └── includes/
│       ├── header.php      # Admin layout header + sidebar
│       └── footer.php      # Admin layout footer
│
├── assets/
│   ├── css/style.css       # Design system (HSL tokens, dark/light, glassmorphism)
│   ├── js/main.js          # Cart, theme toggle, toast notifications
│   └── images/             # Product cover images
│
└── secure/
    └── .htaccess           # Blocks direct file access (Apache)
```

---

## 🛡️ Security Features

- **HMAC-signed download tokens** with 7-day auto-expiry
- **bcrypt password hashing** (PHP `password_hash`)
- **PDO prepared statements** — SQL injection prevention
- **Role-based access control** (Super Admin, Editor, User)
- **Apache `.htaccess`** blocking direct access to `secure/` directory
- **Session-based auth** with account blocking support

---

## 💳 Payment Gateway Simulation

The checkout page simulates Stripe, PayPal, and Razorpay with interactive modals. To use real gateways in production:
1. Go to **Admin → Settings**
2. Enter your real API keys for each gateway
3. Replace the simulation logic in `checkout.php` with the respective gateway SDKs

---

## 📄 License

MIT License — Free for personal and commercial use.

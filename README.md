<div align="center">

# 🛒 ShopLy

### Modern E-Commerce Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.2-FDAE4B?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-Passing-22C55E?style=for-the-badge&logo=phpunit&logoColor=white)](#-testing)
[![Code Style](https://img.shields.io/badge/Code%20Style-PSR--12-8A2BE2?style=for-the-badge)](#-development)

[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat-square&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)

---

**Full-stack e-commerce platform with admin panel, seller dashboard, and customer storefront**

</div>

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Development](#-development)
- [Project Structure](#-project-structure)
- [Admin Panel](#-admin-panel)
- [API Endpoints](#-api-endpoints)
- [Testing](#-testing)
- [Configuration](#-configuration)

---

## ✨ Features

### 🛍️ Customer Storefront

| Feature | Description |
|---------|-------------|
| **Product Catalog** | Browse products with filtering by categories, search, and sorting |
| **Product Variants** | Support for product variations (size, color, etc.) with separate stock |
| **Shopping Cart** | Add/remove items, update quantities, apply discount coupons |
| **Wishlist** | Save products for later with easy add-to-cart |
| **Product Comparison** | Compare multiple products side-by-side |
| **Reviews & Ratings** | Submit product reviews with moderation system |
| **Order Tracking** | Track order status by order number |
| **Recently Viewed** | Quick access to previously viewed products |
| **User Settings** | Comprehensive settings page (Profile, Orders, Addresses, Security, Notifications) |
| **Two-Factor Auth** | Email-based 2FA for enhanced account security |

### 🎛️ Admin Panel (Filament)

| Resource | Capabilities |
|----------|--------------|
| **Products** | CRUD, variants, images, categories, CSV import/export, company assignment |
| **Companies** | Verify/unverify seller companies, view company details |
| **Orders** | Status management, order history, status transitions |
| **Customers** | User management, account details |
| **Coupons** | Fixed/percentage discounts, validity periods, usage limits |
| **Reviews** | Moderate customer reviews (approve/reject) |
| **Tickets** | Customer support with **real-time chat** (3s polling), file uploads, image previews |
| **Refunds** | Process refund requests with status history |
| **Import Jobs** | Monitor bulk product imports with error tracking |
| **Users** | Role management (Super Admin only can assign Admin/Seller roles) |

### 👨‍💼 Seller Panel

Dedicated dashboard for sellers to manage their companies and products:

| Feature | Description |
|---------|-------------|
| **Company Profile** | Create and manage your company (name, description, logo, banner) |
| **Products** | Full product management with automatic company assignment |
| **Orders** | View and manage company orders with status updates |
| **Public Storefront** | Customers can visit `/companies/{slug}` to see company profile |
| **Followers** | Customers can follow companies to stay updated |

### 🏢 Company System

| Feature | Description |
|---------|-------------|
| **Company Profiles** | Each seller can create one company with public profile page |
| **Company Directory** | Browse all companies at `/companies` with search and filters |
| **Follow System** | Users can follow companies they like |
| **Verified Badge** | Admins can verify trusted companies |
| **Product Ownership** | All products belong to a specific company |

### � Role System

| Role | Capabilities |
|------|-------------|
| **Super Admin** | Full system access, assign Admin/Seller roles, manage all resources |
| **Admin** | Manage tickets, orders, products, moderate reviews, verify companies |
| **Seller** | Manage own company, products, and company orders |
| **User** | Browse products, place orders, submit tickets, manage profile |

### �🔔 System Features

| Feature | Description |
|---------|-------------|
| **Real-time Notifications** | In-app notifications with live polling (30s), email notifications via SMTP |
| **Support Tickets** | Real-time chat system with file attachments, image previews, AJAX messaging |
| **Activity Log** | Track user actions across the platform |
| **Role-based Access** | Super Admin, Admin, Seller, User with granular permissions |
| **Multi-language** | English, Russian, Latvian (en, ru, lv) |
| **PDF Invoices** | Generate downloadable invoices (DomPDF) |
| **Dark/Light Theme** | User preference for theme switching |
| **Email Integration** | Gmail/Yandex SMTP support for real email delivery |

---

## 🛠️ Tech Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.2+ | Runtime |
| Laravel | 12.x | Framework |
| Filament | 3.2 | Admin/Seller panels |
| Livewire | 3.x | Reactive components |
| DomPDF | 3.1 | PDF generation |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Alpine.js | 3.x | JavaScript framework |
| Tailwind CSS | 3.x | Styling |
| Vite | 7.x | Build tool |

### Database & Cache

| Technology | Purpose |
|------------|---------|
| MySQL 8.0+ | Primary database |
| SQLite | Testing |
| Redis | Cache & sessions (optional) |

---

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ with npm
- MySQL 8.0+ or SQLite

### Quick Start

```bash
# Clone repository
git clone https://github.com/Vlad8733/LaravelFilament-test.git
cd filament-test

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets
npm run build

# Storage link
php artisan storage:link

# Start server
php artisan serve
```

### Docker Installation

```bash
cp .env.docker .env
make init
```

Access at http://localhost:8080

---

## 💻 Development

### Start Development Server

```bash
# All services at once
composer dev
```

This starts:
- Laravel server (localhost:8000)
- Vite with HMR
- Queue worker
- Log tailing (Pail)

### Individual Commands

```bash
php artisan serve        # Laravel server
npm run dev              # Vite HMR
php artisan queue:work   # Queue worker
php artisan pail         # Log tailing
```

### Build for Production

```bash
npm run build
```

### Code Style

```bash
vendor/bin/pint
```

---

## 📁 Project Structure

```
app/
├── Filament/
│   ├── Resources/           # Admin CRUD resources
│   │   ├── ProductResource  # Products management & moderation
│   │   ├── CompanyResource  # Company verification & moderation
│   │   ├── OrderResource    # Orders management
│   │   ├── CouponResource   # Discount coupons
│   │   ├── UserResource     # Customer accounts
│   │   ├── TicketResource   # Support tickets
│   │   └── ...
│   └── Seller/              # Seller panel
│       └── Resources/       # Seller-specific resources
│           ├── CompanyResource  # Manage own company
│           └── ProductResource  # Manage company products
├── Http/
│   ├── Controllers/         # Web controllers
│   │   ├── CartController   # Shopping cart operations
│   │   ├── ProductController
│   │   ├── CompanyController # Company pages & follow
│   │   ├── WishlistController
│   │   ├── TicketController
│   │   └── ...
│   ├── Livewire/            # Livewire components
│   └── Middleware/          # Custom middleware
├── Models/                  # Eloquent models (20+)
│   ├── Product, ProductVariant, ProductImage
│   ├── Company, CompanyFollow
│   ├── Order, OrderItem, OrderStatus
│   ├── User, CartItem, WishlistItem
│   ├── Coupon, Review, Ticket
│   └── ...
├── Notifications/           # Email & database notifications
├── Observers/               # Model event observers
├── Policies/                # Authorization policies
└── Jobs/                    # Queue jobs (ImportProductsJob)

database/
├── factories/               # Model factories for testing
├── migrations/              # Database schema
└── seeders/                 # Sample data

resources/
├── css/                     # Modular stylesheets
├── js/                      # Alpine.js components
├── lang/                    # Translations (en, ru, lv)
└── views/                   # Blade templates

tests/
├── Feature/                 # Feature tests
└── Unit/                    # Unit tests
```

---

## 🎛️ Admin Panel

Access the admin panel at `/admin` after logging in with an admin account.

### Available Resources

| Resource | Features |
|----------|----------|
| **Products** | Create/edit products, manage variants, upload images, import/export CSV, assign to companies |
| **Companies** | Verify/unverify seller companies, moderate company profiles |
| **Orders** | View order details, update status, view status history |
| **Order Statuses** | Define custom order statuses |
| **Users** | Manage customer accounts, assign roles (Super Admin only) |
| **Coupons** | Create discount codes (fixed/percentage), set validity, usage limits |
| **Customer Reviews** | Approve/reject product reviews |
| **Tickets** | **Real-time chat** with customers, file uploads, auto-refresh every 3s |
| **Refund Requests** | Process customer refund requests |
| **Import Jobs** | Monitor CSV imports, download failed rows |

### Seller Panel Features

Access at `/seller` for users with Seller role:

| Resource | Features |
|----------|----------|
| **Company** | Create/edit company profile, upload logo and banner |
| **Products** | Manage company products with automatic ownership |
| **Orders** | View and update company order statuses |

---

## 🔌 API Endpoints

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | Product listing |
| GET | `/products/{slug}` | Product details |
| GET | `/category/{slug}` | Products by category |
| GET | `/search` | Global search (products & companies) |

### Companies

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/companies` | Browse all companies |
| GET | `/companies/{slug}` | Company profile with products |
| POST | `/companies/{company}/follow` | Follow/unfollow company |

### Cart

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/cart` | View cart |
| POST | `/cart/add/{productId}` | Add to cart |
| PATCH | `/cart/update/{itemId}` | Update quantity |
| DELETE | `/cart/remove/{itemId}` | Remove item |
| GET | `/cart/count` | Get cart count |
| POST | `/cart/coupon/apply` | Apply coupon |
| DELETE | `/cart/coupon/remove` | Remove coupon |

### Wishlist

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wishlist` | View wishlist |
| POST | `/wishlist/add/{productId}` | Add product |
| DELETE | `/wishlist/remove/{productId}` | Remove product |
| GET | `/wishlist/count` | Get count |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/checkout` | Checkout page |
| POST | `/checkout` | Place order |
| GET | `/track-order` | Order tracking form |
| POST | `/track-order` | Search by order number |
| GET | `/track-order/{orderNumber}` | View order status |

### Support

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/support` | List tickets |
| POST | `/support` | Create ticket |
| GET | `/support/{ticket}` | View ticket |
| POST | `/support/{ticket}/reply` | Reply to ticket (AJAX) |
| GET | `/support/{ticket}/check-new-messages` | Poll for new messages (3s) |

### Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | View all notifications |
| GET | `/notifications/count` | Get unread count (30s polling) |
| POST | `/notifications/{id}/read` | Mark as read |
| POST | `/notifications/mark-all-read` | Mark all as read |
| DELETE | `/notifications/{id}` | Delete notification |

---

## 🧪 Testing

The project includes a comprehensive test suite with **84 tests** covering all major features.

### Run Tests

```bash
# Run all tests
php artisan test

# Run with verbose output
php artisan test -v

# Run specific test suite
php artisan test --filter=CartTest
php artisan test --filter=OrderTest

# Using composer script
composer test
```

### Test Coverage

| Suite | Tests | Coverage |
|-------|-------|----------|
| AuthTest | 14 | Registration, login, logout, profile, password |
| CartTest | 12 | Add, update, remove, stock validation |
| OrderTest | 9 | Checkout, order placement, tracking |
| ProductTest | 11 | Listing, details, slug generation |
| WishlistTest | 9 | Add, remove, user isolation |
| CouponTest | 14 | Validation, calculation, apply/remove |
| ReviewTest | 7 | Submit, moderation, queries |
| Import Tests | 6 | Bulk import, failures, variants |

---

## ⚙️ Configuration

### Environment Variables

```env
# Application
APP_NAME=ShopLy
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shoply
DB_USERNAME=root
DB_PASSWORD=

# Queue (required for imports & notifications)
QUEUE_CONNECTION=database

# Mail (Gmail SMTP for production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-digit-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# Alternative: Yandex Mail
# MAIL_HOST=smtp.yandex.ru
# MAIL_PORT=465
# MAIL_ENCRYPTION=ssl

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

### Queue Configuration

Background jobs are used for:
- Bulk product imports
- Email notifications
- Order status notifications

```bash
# Start queue worker
php artisan queue:work

# Or use the dev script which includes queue
composer dev
```

### Email Configuration

For **real email delivery** (even from localhost):

#### Gmail Setup:
1. Enable 2-Step Verification: https://myaccount.google.com/security
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Update `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-digit-app-password
   MAIL_ENCRYPTION=tls
   ```
4. Run: `php artisan config:clear`

#### Notifications Sent:
- Order status changes
- New ticket replies
- Ticket status updates
- Password resets
- Email verification

### Language Configuration

Supported languages: **English** (en), **Russian** (ru), **Latvian** (lv)

Change language via:
- URL: `/language/{locale}` (en, ru, lv)
- Session-based preference

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

<div align="center">

### Built with

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-FDAE4B?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Tailwind](https://img.shields.io/badge/Tailwind-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

</div>

<![CDATA[<div align="center">

<!-- Hero Section -->
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">

<br><br>

# 🛒 ShopLy

### ✨ Modern E-Commerce Platform ✨

<p align="center">
  <strong>Full-featured marketplace with seller companies, admin panel & customer storefront</strong>
</p>

<br>

<!-- Badges Row 1 - Main Technologies -->
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.2-FDAE4B?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-84%20passed-22C55E?style=for-the-badge&logo=phpunit&logoColor=white)](#-testing)

<!-- Badges Row 2 - Frontend -->
<br>

[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat-square&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)

<br>

<!-- Quick Links -->
[📖 Documentation](#-table-of-contents) •
[🚀 Quick Start](#-quick-start) •
[✨ Features](#-features) •
[🧪 Testing](#-testing)

</div>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 📋 Table of Contents

<details open>
<summary><b>Click to expand</b></summary>

- [✨ Features](#-features)
  - [🛍️ Customer Storefront](#️-customer-storefront)
  - [🏢 Company System](#-company-system)
  - [🎛️ Admin Panel](#️-admin-panel)
  - [👨‍💼 Seller Panel](#-seller-panel)
- [🛠️ Tech Stack](#️-tech-stack)
- [🚀 Quick Start](#-quick-start)
- [💻 Development](#-development)
- [📁 Project Structure](#-project-structure)
- [🔌 API Reference](#-api-reference)
- [🧪 Testing](#-testing)
- [⚙️ Configuration](#️-configuration)

</details>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## ✨ Features

<div align="center">

### 🏗️ Platform Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        🌐 ShopLy Platform                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐            │
│   │  👤 Buyer   │    │  🏪 Seller  │    │  👑 Admin   │            │
│   │  Storefront │    │    Panel    │    │    Panel    │            │
│   └──────┬──────┘    └──────┬──────┘    └──────┬──────┘            │
│          │                  │                  │                    │
│          ▼                  ▼                  ▼                    │
│   ┌─────────────────────────────────────────────────────┐          │
│   │              🏢 Company-Based Architecture           │          │
│   │  ┌─────────┐  ┌─────────┐  ┌─────────┐              │          │
│   │  │Company A│  │Company B│  │Company C│  ...         │          │
│   │  │📦📦📦   │  │📦📦     │  │📦📦📦📦 │              │          │
│   │  └─────────┘  └─────────┘  └─────────┘              │          │
│   └─────────────────────────────────────────────────────┘          │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

</div>

<br>

### 🛍️ Customer Storefront

<table>
<tr>
<td width="50%">

#### 🔍 Discovery
- **Product Catalog** with filters & search
- **Category Navigation** with hierarchy
- **Company Directory** — browse all sellers
- **Recently Viewed** products

</td>
<td width="50%">

#### 🛒 Shopping
- **Shopping Cart** with quantity management
- **Wishlist** for saving favorites
- **Product Comparison** side-by-side
- **Discount Coupons** support

</td>
</tr>
<tr>
<td width="50%">

#### ⭐ Engagement
- **Product Reviews** & ratings
- **Follow Companies** you love
- **Order Tracking** by number
- **Support Tickets** system

</td>
<td width="50%">

#### 🎨 Experience
- **Dark/Light Theme** toggle
- **Multi-language** (EN, RU, LV)
- **Responsive Design** mobile-first
- **Fast Search** products & companies

</td>
</tr>
</table>

<br>

### 🏢 Company System

> **New!** Role-based seller system with company profiles

<table>
<tr>
<td align="center" width="25%">
<br>
<img width="60" src="https://cdn-icons-png.flaticon.com/512/1299/1299949.png" alt="Company">
<br><br>
<b>Company Profiles</b>
<br>
<sub>Logo, banner, description, contacts</sub>
<br><br>
</td>
<td align="center" width="25%">
<br>
<img width="60" src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Follow">
<br><br>
<b>Follow System</b>
<br>
<sub>Users can follow favorite sellers</sub>
<br><br>
</td>
<td align="center" width="25%">
<br>
<img width="60" src="https://cdn-icons-png.flaticon.com/512/6941/6941697.png" alt="Verified">
<br><br>
<b>Verification</b>
<br>
<sub>Admin-verified trusted companies</sub>
<br><br>
</td>
<td align="center" width="25%">
<br>
<img width="60" src="https://cdn-icons-png.flaticon.com/512/3081/3081559.png" alt="Products">
<br><br>
<b>Product Ownership</b>
<br>
<sub>All products belong to companies</sub>
<br><br>
</td>
</tr>
</table>

```
📍 /companies          → Browse all companies with search & filters
📍 /companies/{slug}   → Company profile page with products
📍 /seller             → Seller panel to manage your company
```

<br>

### 🎛️ Admin Panel

> Access at `/admin` — Full control over the platform

<table>
<tr>
<th>📦 Catalog</th>
<th>📋 Orders</th>
<th>👥 Users</th>
<th>🔧 System</th>
</tr>
<tr>
<td valign="top">

- Products (CRUD, import/export)
- Product variants & images
- Categories management
- Companies moderation

</td>
<td valign="top">

- Order management
- Status transitions
- Order history
- Refund requests

</td>
<td valign="top">

- Customer accounts
- Role management
- Activity logs
- Support tickets

</td>
<td valign="top">

- Import jobs monitor
- Coupon management
- Review moderation
- Company verification

</td>
</tr>
</table>

<br>

### 👨‍💼 Seller Panel

> Access at `/seller` — Dedicated dashboard for sellers

| Feature | Description |
|:-------:|-------------|
| 🏪 | **Company Profile** — Create your company with logo, banner, description |
| 📦 | **Product Management** — Full CRUD with variants and images |
| 📊 | **Dashboard** — Overview of your company stats |
| 🔗 | **Public Storefront** — Customers visit `/companies/your-slug` |

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 🛠️ Tech Stack

<div align="center">

### Backend

| | Technology | Version | Purpose |
|:-:|:-----------|:-------:|:--------|
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-plain.svg"> | **PHP** | 8.2+ | Runtime |
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg"> | **Laravel** | 12.x | Framework |
| 🟠 | **Filament** | 3.2 | Admin Panel |
| 💜 | **Livewire** | 3.x | Reactive Components |
| 📄 | **DomPDF** | 3.1 | PDF Generation |

### Frontend

| | Technology | Version | Purpose |
|:-:|:-----------|:-------:|:--------|
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/alpinejs/alpinejs-original.svg"> | **Alpine.js** | 3.x | JavaScript Framework |
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg"> | **Tailwind CSS** | 3.x | Utility-first CSS |
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vitejs/vitejs-original.svg"> | **Vite** | 7.x | Build Tool + HMR |

### Database

| | Technology | Version | Notes |
|:-:|:-----------|:-------:|:------|
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg"> | **MySQL** | 8.0+ | Recommended |
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg"> | **PostgreSQL** | 14+ | Supported |
| <img width="20" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/sqlite/sqlite-original.svg"> | **SQLite** | 3.x | Development |

</div>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 🚀 Quick Start

<div align="center">

### Choose Your Installation Method

</div>

<table>
<tr>
<td width="50%" valign="top">

### 🐳 Docker (Recommended)

```bash
# Clone repository
git clone <repository-url>
cd filament-test

# Start everything with one command
cp .env.docker .env
make init
```

**🌐 Access:** http://localhost:8080

<details>
<summary><b>📋 Docker Commands</b></summary>

| Command | Description |
|---------|-------------|
| `make up` | Start containers |
| `make down` | Stop containers |
| `make shell` | Open app shell |
| `make logs` | View logs |
| `make test` | Run tests |
| `make fresh` | Fresh migration |
| `make pint` | Fix code style |

</details>

</td>
<td width="50%" valign="top">

### 💻 Local Setup

```bash
# Clone repository
git clone <repository-url>
cd filament-test

# Automated setup
composer setup

# Or manual:
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan storage:link

# Start server
php artisan serve
```

**🌐 Access:** http://localhost:8000

</td>
</tr>
</table>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 💻 Development

### 🔥 Start Development Server

```bash
composer dev
```

<details>
<summary><b>This starts all services concurrently:</b></summary>

| Service | Description |
|---------|-------------|
| 🌐 **Laravel** | Development server at `localhost:8000` |
| ⚡ **Vite** | Hot Module Replacement |
| 📋 **Queue** | Background job processing |
| 📝 **Pail** | Real-time log tailing |

</details>

### 🏗️ Build for Production

```bash
npm run build
```

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 📁 Project Structure

```
📦 shoply/
├── 🎨 app/
│   ├── Filament/
│   │   ├── Resources/              # 👑 Admin panel resources
│   │   │   ├── ProductResource     #    Products (moderation)
│   │   │   ├── CompanyResource     #    Company verification
│   │   │   ├── OrderResource       #    Order management
│   │   │   └── ...
│   │   └── Seller/                 # 🏪 Seller panel
│   │       └── Resources/
│   │           ├── CompanyResource #    Own company management
│   │           └── ProductResource #    Company products
│   │
│   ├── Http/Controllers/           # 🌐 Web controllers
│   │   ├── CompanyController       #    Company pages & follow
│   │   ├── ProductController       #    Product catalog
│   │   ├── CartController          #    Shopping cart
│   │   └── ...
│   │
│   ├── Models/                     # 📊 Eloquent models (20+)
│   │   ├── Company                 #    Seller companies
│   │   ├── CompanyFollow           #    Follow relationships
│   │   ├── Product                 #    Products (→ Company)
│   │   ├── Order                   #    Customer orders
│   │   └── ...
│   │
│   ├── Notifications/              # 📧 Email & in-app notifications
│   ├── Observers/                  # 👀 Model event observers
│   ├── Policies/                   # 🔐 Authorization policies
│   └── Jobs/                       # ⚙️ Background jobs
│
├── 🗄️ database/
│   ├── factories/                  # Test data factories
│   ├── migrations/                 # Database schema
│   └── seeders/                    # Sample data
│
├── 🎨 resources/
│   ├── css/                        # Modular stylesheets
│   ├── js/                         # Alpine.js components
│   ├── lang/                       # Translations (en, ru, lv)
│   └── views/                      # Blade templates
│
└── 🧪 tests/
    ├── Feature/                    # Feature tests (84 tests)
    └── Unit/                       # Unit tests
```

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 🔌 API Reference

<details open>
<summary><b>🏢 Companies</b></summary>

| Method | Endpoint | Description |
|:------:|----------|-------------|
| `GET` | `/companies` | Browse all companies |
| `GET` | `/companies/{slug}` | Company profile with products |
| `POST` | `/companies/{company}/follow` | Follow/unfollow company |

</details>

<details>
<summary><b>📦 Products</b></summary>

| Method | Endpoint | Description |
|:------:|----------|-------------|
| `GET` | `/products` | Product listing |
| `GET` | `/products/{slug}` | Product details |
| `GET` | `/category/{slug}` | Products by category |
| `GET` | `/search` | Global search (products & companies) |

</details>

<details>
<summary><b>🛒 Cart</b></summary>

| Method | Endpoint | Description |
|:------:|----------|-------------|
| `GET` | `/cart` | View cart |
| `POST` | `/cart/add/{productId}` | Add to cart |
| `PATCH` | `/cart/update/{itemId}` | Update quantity |
| `DELETE` | `/cart/remove/{itemId}` | Remove item |
| `POST` | `/cart/coupon/apply` | Apply coupon |
| `DELETE` | `/cart/coupon/remove` | Remove coupon |

</details>

<details>
<summary><b>❤️ Wishlist</b></summary>

| Method | Endpoint | Description |
|:------:|----------|-------------|
| `GET` | `/wishlist` | View wishlist |
| `POST` | `/wishlist/add/{productId}` | Add product |
| `DELETE` | `/wishlist/remove/{productId}` | Remove product |

</details>

<details>
<summary><b>📋 Orders</b></summary>

| Method | Endpoint | Description |
|:------:|----------|-------------|
| `GET` | `/checkout` | Checkout page |
| `POST` | `/checkout` | Place order |
| `GET` | `/track-order` | Order tracking form |
| `POST` | `/track-order` | Search by order number |

</details>

<details>
<summary><b>🎫 Support</b></summary>

| Method | Endpoint | Description |
|:------:|----------|-------------|
| `GET` | `/support` | List tickets |
| `POST` | `/support` | Create ticket |
| `GET` | `/support/{ticket}` | View ticket |
| `POST` | `/support/{ticket}/reply` | Reply to ticket |

</details>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 🧪 Testing

<div align="center">

### ✅ 84 Tests Passing

</div>

```bash
# Run all tests
php artisan test

# Run specific suite
php artisan test --filter=CartTest

# Using composer
composer test
```

<details>
<summary><b>📊 Test Coverage by Suite</b></summary>

| Suite | Tests | Coverage |
|:------|:-----:|:---------|
| 🔐 **AuthTest** | 14 | Registration, login, logout, profile |
| 🛒 **CartTest** | 12 | Add, update, remove, stock validation |
| 📦 **OrderTest** | 9 | Checkout, order placement, tracking |
| 🏷️ **ProductTest** | 11 | Listing, details, slug generation |
| ❤️ **WishlistTest** | 9 | Add, remove, user isolation |
| 🎟️ **CouponTest** | 14 | Validation, calculation, apply/remove |
| ⭐ **ReviewTest** | 7 | Submit, moderation, queries |
| 📥 **ImportTest** | 6 | Bulk import, failures, variants |

</details>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## ⚙️ Configuration

<details>
<summary><b>📝 Environment Variables</b></summary>

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

# Queue (for imports & notifications)
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

</details>

<details>
<summary><b>🌍 Languages</b></summary>

Supported: **English** (en), **Russian** (ru), **Latvian** (lv)

Change via URL: `/language/{locale}`

</details>

<details>
<summary><b>⚡ Queue Worker</b></summary>

Required for background processing:

```bash
php artisan queue:work
```

Used for:
- 📥 Bulk product imports
- 📧 Email notifications
- 🔔 Order status notifications

</details>

<br>

<!-- Divider -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<br>

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

<br>

<!-- Footer -->
<div align="center">

### 🛠️ Built With Love Using

<br>

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-FDAE4B?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Tailwind](https://img.shields.io/badge/Tailwind-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

<br>

---

<sub>Made with ❤️ by the ShopLy Team</sub>

</div>
]]>

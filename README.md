<div align="center">
  <a href="https://recapet.com/" target="_blank">
    <img src="https://recapet.com/wp-content/uploads/2022/12/Recapet-Logo.svg" width="300" alt="Recapet Logo">
  </a>
  <h1>💰 Recapet Wallet Service 💰</h1>
  <p><em>A secure, audit-friendly wallet service designed for reliability, monitoring, and financial consistency</em></p>
</div>

<div align="center">
  <a href="#-features">Features</a> •
  <a href="#-architecture">Architecture</a> •
  <a href="#-getting-started">Quick Start</a> •
  <a href="#-docker-setup">Docker Setup</a> •
  <a href="#-monitoring--health">Monitoring</a> •
  <a href="#-design-decisions">Design Decisions</a>
</div>

<br>

## 📋 Overview

This wallet service implements a complete and secure financial transaction system built with **Laravel 11**.  
It ensures **consistency**, **auditability**, and **high reliability** through:

-   **Token-based authentication** with Laravel Sanctum
-   **Automatic wallet creation** for each user
-   **Deposits, withdrawals, and P2P transfers** with dynamic fees
-   **Idempotent and concurrent-safe transactions**
-   **Immutable ledger** for full audit tracking
-   **Automated balance snapshots**
-   **Real-time monitoring** powered by **Laravel Telescope**

---

## ✨ Core Features

### 1. Authentication & Authorization

-   Email + password registration and login (via Sanctum tokens)
-   Automatic wallet creation upon registration
-   PIN-based protection for financial transactions
-   Secure password and PIN hashing

### 2. Wallet Operations

#### 💰 Deposits

-   Add funds with cent-level precision
-   Permanent immutable ledger entry per deposit
-   Idempotency key support for retry safety

#### 🏧 Withdrawals

-   Prevent overdrafts with strict balance checks
-   Track transaction status (pending/success/failed)
-   Idempotent and secure

#### 💸 Peer-to-Peer Transfers

-   Internal transfers between users
-   Fee system:
    -   ≤ $25.00 → No fee
    -   > $25.00 → $2.50 + 10%
-   Fully atomic transactions using DB locking

---

### 3. Reliability & Safety

#### 🔒 Concurrency Protection

-   Uses `SELECT ... FOR UPDATE` for pessimistic locking
-   Prevents race conditions and double spending

#### 🔄 Idempotency

-   `idempotency_key` field on financial routes
-   Cache-based deduplication (24-hour retention)

#### 📖 Immutable Ledger

-   Append-only transaction history
-   Never updated or deleted
-   Audit-friendly structure

#### 📊 Balance Snapshots

-   `php artisan wallet:snapshot-balances`
-   Can be scheduled or run manually

---

### 4. Security Highlights

-   PIN verification for all financial ops
-   Rate limiting (10 requests/min)
-   IP and event logging
-   Encrypted data in transit and at rest
-   HTTPS enforcement in production

---

## 🧠 Monitoring & Health

### 🧩 Laravel Telescope

This project integrates **Laravel Telescope** for real-time monitoring, debugging, and auditing.  
It tracks:

-   Requests and responses
-   Database queries and exceptions
-   Logs, jobs, events, and commands

Access it at:

```
/telescope
```

> Telescope provides complete insight into the system’s behavior, making it ideal for debugging and auditing.

### 🩺 Health Check Endpoint

Check service status and DB connection:

```
GET /api/healthz
```

Example response:

```json
{
    "status": "ok",
    "database": "connected",
    "time": "2025-10-24 23:00:00"
}
```

---

## 🐳 Docker Setup

This project supports full containerization for quick setup and consistent environments.

### 🔧 Prerequisites

-   Docker & Docker Compose installed

### ⚙️ Build and Run

1. **Clone the repository**

```bash
git clone git@github.com:Dev-Ahmed-Alaa/wallet-service.git
or git clone https://github.com/Dev-Ahmed-Alaa/wallet-service.git
cd wallet-service
```

2. **Build and start containers**

```bash
docker-compose up -d
```

3. **Run database migrations and seed data**

```bash
docker-compose exec app php artisan migrate --seed
```

4. **Access the application**

-   API: [http://localhost:8000](http://localhost:8000)
-   Telescope: [http://localhost:8000/telescope](http://localhost:8000/telescope)

5. **Run tests (optional)**

```bash
docker-compose exec app php artisan test
```

---

## 🏗️ Architecture

The service follows a **layered architecture** with clear separation of concerns:

```
Controllers → Services → Repositories → Models → Database
```

-   **Controllers**: Handle requests & validation
-   **Services**: Business logic (Wallet, Auth, Ledger)
-   **Repositories**: Data access abstraction
-   **Models**: Represent domain entities
-   **Ledger**: Immutable transaction tracking

---

## 🧪 Testing

Comprehensive feature and unit tests cover:

-   Registration and authentication
-   Deposits, withdrawals, and transfers
-   Fee calculation
-   Idempotency
-   Concurrency and balance consistency

Run:

```bash
php artisan test
```

or via Docker:

```bash
docker-compose exec app php artisan test
```

---

## 🚀 Getting Started (Manual Setup)

1. **Clone the repo**

```bash
git clone https://github.com/yourusername/recapet-wallet-challenge.git
cd recapet-wallet-challenge
```

2. **Install dependencies**

```bash
composer install
```

3. **Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Run migrations**

```bash
php artisan migrate --seed
```

5. **Start the local server**

```bash
php artisan serve
```

Access the API at `http://localhost:8000`.

---

## 🛠️ Tech Stack

-   **Framework:** Laravel 12
-   **Auth:** Sanctum
-   **Monitoring:** Laravel Telescope
-   **Database:** MySQL
-   **Testing:** PHPUnit
-   **Containerization:** Docker + Docker Compose

---

## 🔒 Security Summary

-   Passwords & PINs hashed (bcrypt)
-   Rate limiting on critical endpoints
-   No sensitive data in logs
-   HTTPS enforced in production
-   Immutable ledger ensures audit compliance

---

## 📝 Notes

-   Telescope enabled only in local/testing environments (not production).
-   Docker setup includes PHP-FPM, Nginx, and MySQL services.
-   Default test user:
    -   **Email:** menna.rateb@recapet.com
    -   **Password:** Recapet@123
    -   **PIN:** 123456

---

<div align="center">
  <p>🚀 Built for the <strong>Recapet Coding Challenge</strong></p>
  <p><em>Reliable • Secure • Audit-Ready • Monitored</em></p>
  <p>🔗 <a href="https://recapet.com/">Recapet</a></p>
</div>

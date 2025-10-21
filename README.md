<div align="center">
  <a href="https://recapet.com/" target="_blank">
    <img src="https://recapet.com/wp-content/uploads/2022/12/Recapet-Logo.svg" width="300" alt="Recapet Logo">
  </a>
  <h1>💰 My Wallet Service Project 💰</h1>
  <p><em>A secure, reliable digital wallet system with a focus on consistency and auditability</em></p>
</div>

<div align="center">
  <a href="#-features">Features</a> •
  <a href="#-architecture">Architecture</a> •
  <a href="#-api-design">API</a> •
  <a href="#-implementation-plan">Implementation</a> •
  <a href="#-tech-stack">Tech Stack</a>
</div>

<br>

## 🚀 Project Vision

I've designed this wallet service to handle financial transactions with rock-solid reliability. My goal was to create a system that maintains perfect consistency even under heavy load, while providing a comprehensive audit trail for every penny moved through the platform.

The service enables users to create accounts, manage personal wallets, deposit and withdraw funds, and transfer money to other users - all with enterprise-grade security and performance.

## ✨ Features

<table>
  <tr>
    <td align="center">👤</td>
    <td><strong>User Management</strong></td>
    <td>Secure registration and authentication with token-based API access. I've implemented industry-standard password hashing to keep user credentials safe.</td>
  </tr>
  <tr>
    <td align="center">💼</td>
    <td><strong>Automatic Wallets</strong></td>
    <td>Each new user automatically receives their own wallet upon registration - simple and frictionless onboarding.</td>
  </tr>
  <tr>
    <td align="center">💵</td>
    <td><strong>Deposits</strong></td>
    <td>Users can easily add funds with real-time balance updates and permanent transaction records.</td>
  </tr>
  <tr>
    <td align="center">🏧</td>
    <td><strong>Withdrawals</strong></td>
    <td>Smart balance validation prevents overdrafts, with comprehensive status tracking for every transaction.</td>
  </tr>
  <tr>
    <td align="center">↔️</td>
    <td><strong>P2P Transfers</strong></td>
    <td>Send money to other users with intelligent fee calculation (transfers above $25 incur a fee of $2.50 + 10%).</td>
  </tr>
  <tr>
    <td align="center">🔄</td>
    <td><strong>Idempotent Requests</strong></td>
    <td>My unique request identifier system prevents duplicate transfers if a client retries a request.</td>
  </tr>
  <tr>
    <td align="center">⚡</td>
    <td><strong>Concurrency Protection</strong></td>
    <td>Advanced locking mechanisms prevent double-spending and maintain accurate balances under high load.</td>
  </tr>
  <tr>
    <td align="center">📒</td>
    <td><strong>Immutable Ledger</strong></td>
    <td>Every financial movement is recorded in a one-way ledger that can never be modified or deleted.</td>
  </tr>
  <tr>
    <td align="center">📊</td>
    <td><strong>Balance Snapshots</strong></td>
    <td>Periodic balance snapshots enable historical reconciliation and auditing.</td>
  </tr>
  <tr>
    <td align="center">🔍</td>
    <td><strong>Monitoring</strong></td>
    <td>Comprehensive logging and health checks make troubleshooting a breeze.</td>
  </tr>
</table>

## 🛡️ Security & Quality Focus

I've built this system with security and precision as top priorities:

-   **Money Precision**: Cent-level accuracy with proper rounding for all financial calculations
-   **Data Protection**: End-to-end encryption for sensitive data both in transit and at rest
-   **Access Control**: Rate-limiting on critical endpoints and strict authentication for protected routes
-   **Comprehensive Testing**: Automated test suite covering business rules, error handling, and concurrent operations

## 🏗️ Architecture

I've designed a clean, modular architecture that separates concerns and enables future scaling:

### Data Models

<div align="center">
  <img src="https://via.placeholder.com/800x400?text=My+Data+Model+Design" width="80%" alt="Data Model Diagram">
</div>

-   **User**: Authentication and profile information
-   **Wallet**: Balance tracking with status management
-   **LedgerEntry**: Immutable financial transaction records
-   **Transfer**: P2P transfer details with fee calculation
-   **IdempotencyKey**: Request deduplication system
-   **BalanceSnapshot**: Point-in-time balance records

### Key Components

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│  API Layer      │      │  Service Layer  │      │  Data Layer     │
│                 │      │                 │      │                 │
│  - Controllers  │─────▶│  - WalletSvc    │─────▶│  - Repositories │
│  - Middleware   │      │  - TransferSvc  │      │  - Models       │
│  - Validation   │◀─────│  - LedgerSvc    │◀─────│  - Database     │
└─────────────────┘      └─────────────────┘      └─────────────────┘
```

## 🔌 API Design

I've created a RESTful API with intuitive endpoints:

### Authentication

-   `POST /api/auth/register` - Create new user account
-   `POST /api/auth/login` - Obtain access token
-   `POST /api/auth/logout` - Invalidate token

### Wallet Operations

-   `GET /api/wallet` - View wallet details
-   `GET /api/wallet/transactions` - View transaction history
-   `POST /api/transactions/deposit` - Add funds
-   `POST /api/transactions/withdraw` - Remove funds
-   `POST /api/transactions/transfer` - Send to another user

### Monitoring

-   `GET /healthz` - Service health check
-   `GET /api/admin/metrics` - System metrics (admin only)

## 📝 Implementation Plan

I've broken down the development into logical phases:

1. **Foundation** - User auth, wallet creation, database schema
2. **Transactions** - Deposit, withdrawal, and transfer functionality
3. **Consistency** - Concurrency handling and idempotency
4. **Audit** - Ledger implementation and balance snapshots
5. **Polish** - Testing, documentation, and performance optimization

## 🚀 Getting Started

```bash
# Clone the repository
git clone https://github.com/yourusername/wallet-service.git

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Start the development server
php artisan serve
```

---

<div align="center">
  <p>Designed with ❤️ by <a href="https://recapet.com/">Recapet</a> Challenge Participant</p>
</div>

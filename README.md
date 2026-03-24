# BankApp - Secure Financial Transfer API

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)

A high-performance, secure financial engine for handling multi-currency accounts and peer-to-peer transfers. Built with a focus on data integrity, concurrency safety, and modern software design patterns.

## 🚀 Key Features

-   **Multi-Currency Support**: Each user can have multiple accounts in different currencies (USD, EUR, etc.).
-   **Precision Arithmetic**: Powered by [MoneyPHP](https://github.com/moneyphp/money) to avoid floating-point errors.
-   **Concurrency Safety**: Implements row-level database locking (`SELECT FOR UPDATE`) to prevent double-spending and race conditions.
-   **Idempotency Layer**: Support for `Idempotency-Key` headers to ensure failed/retried requests don't execute twice.
-   **Robust Architecture**: 
    -   **Double-Entry** ready ledger structure.
    -   **Domain Exceptions**: Custom business logic errors with consistent JSON responses.
    -   **Thin Controllers**: Logic encapsulated in dedicated Services and FormRequests.
-   **Security**: API Rate Limiting (throttling) for sensitive operations.

## 🛠 Tech Stack

-   **Backend**: Laravel 12 (PHP 8.2+)
-   **Database**: PostgreSQL
-   **Containerization**: Docker (Laravel Sail)
-   **Financial Library**: MoneyPHP
-   **Authentication**: Laravel Sanctum

## 🏁 Getting Started

### Prerequisites
-   Docker Desktop installed on your system.

### Installation

1.  **Clone and Configure**:
    ```powershell
    copy .env.example .env
    ```

2.  **Start Services**:
    ```bash
    ./vendor/bin/sail up -d
    ```

3.  **Setup Environment**:
    ```bash
    ./vendor/bin/sail composer install
    ./vendor/bin/sail php artisan key:generate
    ./vendor/bin/sail php artisan migrate:fresh --seed
    ```
    *The seeding command generates 10 test users and base currencies (USD, EUR, RUB).*

## 🧪 Testing

Run the feature test suite to ensure system integrity:
```bash
./vendor/bin/sail php artisan test
```

## 🔌 API Reference (v1)

### Authentication
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/register` | Create a new user account |
| `POST` | `/api/v1/login` | Obtain a Bearer Token |
| `POST` | `/api/v1/logout` | Revoke current token |

### Financial Operations
All financial requests require `Authorization: Bearer <token>` and an optional `Idempotency-Key`.

#### 💰 Deposit
`POST /api/v1/users/{id}/deposit`
-   **Payload**: `{"amount": 150.50}`
-   Deposits funds into the user's default account.

#### 💸 Transfer
`POST /api/v1/transfers`
-   **Payload**: `{"receiver_id": 5, "amount": 25.00}`
-   Moves funds from the sender's default account to the receiver's default account.
-   **Safety**: Automatically blocks self-transfers and overdrafts.

## 🛡 Security Design

-   **Race Condition Prevention**: Enforces a strict locking order (by account IDs) to prevent deadlocks during high-load concurrent transfers.
-   **Audit Trail**: Transactions store metadata including `ip_address`, `user_agent`, and `idempotency_key`.
-   **Data Consistency**: Database-level `CHECK` constraints on balances to ensure they never fall below zero.

---
*Created for secure and scalable financial applications.*
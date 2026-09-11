# Dentify - Backend API

[![Symfony](https://img.shields.io/badge/Symfony-000000?style=for-the-badge\&logo=symfony\&logoColor=white)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge\&logo=php\&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
[![Frontend Repo](https://img.shields.io/badge/Frontend-Dentify--Frontend-DD0031?style=for-the-badge\&logo=angular\&logoColor=white)](https://github.com/DiegoPerezPonce/dentify-frontend)

**Dentify Backend** is the core RESTful API service powering the Dentify dental clinic management ecosystem. Built with the **Symfony framework** and **PHP**, it provides business logic, clinical history tracking, treatment workflows, and persistent state management for tooth-level odontograms.

---

## 📋 Table of Contents

* [Features](#-features)
* [Architecture & Project Structure](#-architecture--project-structure)
* [Tech Stack](#-tech-stack)
* [API Endpoints Overview](#-api-endpoints-overview)
* [Getting Started](#-getting-started)

  * [Prerequisites](#prerequisites)
  * [Installation & Setup](#installation--setup)
  * [Database Configuration](#database-configuration)
* [Development Commands](#-development-commands)
* [Related Repositories](#-related-repositories)
* [Contributors](#-contributors)
* [License](#-license)

---

## 🌟 Features

* 👤 **Patient Records Management:** CRUD operations for patient profiles, clinical notes, and medical backgrounds.
* 🦷 **Odontogram Data Engine:** Persistence and real-time mapping of individual tooth conditions, procedures, and historical changes.
* 🩺 **Treatment Catalog:** Management of clinical procedures, pricing structures, and assignment to patient charts.
* 🔒 **Security & Authentication:** Role-based access control (RBAC), JWT authentication, and secure CORS handling for frontend requests.
* 🗄️ **Robust Data Architecture:** Relational mapping powered by Doctrine ORM, featuring database migrations and data fixtures.

---

## 🏗️ Architecture & Project Structure

The project follows standard Symfony practices and domain-driven directory conventions:

```text
dentify-backend/
├── config/                # Framework, bundle, security, and routing configurations
├── migrations/            # Doctrine ORM database migration files
├── public/                # Web server entry point (index.php)
├── src/
│   ├── Controller/        # REST API endpoints & route handlers
│   ├── DTO/               # Data Transfer Objects for request/response handling
│   ├── Entity/            # Doctrine ORM database entities
│   ├── EventSubscriber/   # Request/Response event listeners & middleware
│   ├── Repository/        # Custom database queries and data access logic
│   └── Service/           # Domain business logic services
├── tests/                 # PHPUnit test suites
├── .env                   # Base environment configuration
├── composer.json          # Dependency management manifest
└── symfony.lock           # Symfony bundle lock file
```

---

## 🛠️ Tech Stack

* **Framework:** [Symfony](https://symfony.com/)
* **Language:** [PHP 8.2+](https://www.php.net/)
* **ORM & Database Tooling:** Doctrine ORM, Doctrine Migrations
* **Authentication:** JWT with LexikJWTAuthenticationBundle and Symfony Security
* **Dependency Manager:** [Composer](https://getcomposer.org/)
* **Database:** MySQL / PostgreSQL
* **Testing:** PHPUnit

---

## 📡 API Endpoints Overview

Sample overview of the primary RESTful endpoints exposed by the Symfony API:

| HTTP Method | Endpoint                           | Description                                    |
| :---------: | ---------------------------------- | ---------------------------------------------- |
|    `GET`    | `/api/v1/patients`                 | Retrieve list of registered patients           |
|    `POST`   | `/api/v1/patients`                 | Create a new patient profile                   |
|    `GET`    | `/api/v1/patients/{id}/odontogram` | Fetch odontogram state for a specific patient  |
|    `PUT`    | `/api/v1/patients/{id}/odontogram` | Update tooth status or procedures              |
|    `GET`    | `/api/v1/treatments`               | Fetch complete catalog of available treatments |
|    `POST`   | `/api/v1/treatments`               | Add a new procedure/treatment to the catalog   |

> **Note:** The endpoints above provide a high-level overview of the API. Refer to the source code for the complete list of available routes.

---

## 🚀 Getting Started

### Prerequisites

Ensure you have the following installed locally:

* **PHP:** `8.2` or higher with the required extensions:

  * `pdo_mysql` / `pdo_pgsql`
  * `mbstring`
  * `iconv`
  * `ctype`
* **Composer:** `2.x`
* **Database Server:** MySQL 8.0+ or PostgreSQL 14+
* **Symfony CLI:** Recommended for local development

You can install Symfony CLI by following the official documentation:

https://symfony.com/download

---

### Installation & Setup

#### 1. Clone the repository

```bash
git clone https://github.com/DiegoPerezPonce/dentify-backend.git
cd dentify-backend
```

#### 2. Install PHP dependencies

```bash
composer install
```

#### 3. Configure environment variables

Create a `.env.local` file in the project root to override database and application configuration:

```dotenv
APP_ENV=dev
APP_SECRET=your_secret_key_here

# Database connection string
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/dentify_db?serverVersion=8.0&charset=utf8mb4"

# CORS configuration for Angular frontend
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

> **Important:** Never commit `.env.local` or other files containing real credentials, secrets, passwords, or private keys.

---

## 🗄️ Database Configuration

### 1. Create the database

```bash
php bin/console doctrine:database:create
```

### 2. Run database migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 3. Load initial fixtures

If the project contains Doctrine fixtures:

```bash
php bin/console doctrine:fixtures:load
```

### 4. Start the local development server

Using Symfony CLI:

```bash
symfony server:start
```

Alternatively, using PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

The API will be available at:

```text
http://127.0.0.1:8000/
```

---

## 🧪 Development Commands

| Action               | Command                                       | Description                                 |
| -------------------- | --------------------------------------------- | ------------------------------------------- |
| **Start Server**     | `symfony server:start`                        | Starts the local Symfony development server |
| **Run Migrations**   | `php bin/console doctrine:migrations:migrate` | Applies pending database migrations         |
| **Create Migration** | `php bin/console make:migration`              | Generates a migration from Entity changes   |
| **Clear Cache**      | `php bin/console cache:clear`                 | Clears the Symfony cache                    |
| **Run Tests**        | `php bin/phpunit`                             | Executes the PHPUnit test suite             |

---

## 🔗 Related Repositories

* **Frontend Application:** [dentify-frontend](https://github.com/DiegoPerezPonce/dentify-frontend)

---

## 📄 License

This project is licensed under the **MIT License**.

See the [`LICENSE`](LICENSE) file for details.

# EXPIR0 (su expir0.it)

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-10.x%2F11.x-FF2D20?style=flat-square&logo=laravel)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker)
![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?style=flat-square&logo=mariadb)
![Security](https://img.shields.io/badge/WebAuthn-Passkey-success?style=flat-square)

An enterprise web application developed in Laravel for the centralization, monitoring, and automated notification of **any type of deadline**. 

Born out of the need to manage critical dates without category restrictions, the system is suitable for tracking physical assets (vehicles, real estate), documentation (driver’s licenses, passports), contracts, insurance policies, software subscriptions, and tax compliance. Designed with a production-ready containerized architecture, it places a strong emphasis on data security and the reliability of asynchronous notifications.

## ⚙️ Key Features (MVP)

* **Agnostic Deadline Management:** A flexible engine that allows users to enter and categorize time-sensitive events of any kind.
* **Proactive Alert System:** A dedicated background worker that evaluates upcoming deadlines daily and sends automated email notifications to affected users.
* **Biometric Authentication (Passwordless):** Implementation of the **WebAuthn / Passkey** standard. Enables access via facial recognition (FaceID/Windows Hello) or fingerprint, eliminating risks associated with credential theft.
* **Analytics Dashboard:** Clean user interface for a quick overview of the status of deadlines (current, expiring, expired).

## 🏗️ Architecture and Infrastructure

The project is not just an application, but a complete infrastructure defined via code (`Infrastructure as Code`) using **Docker** and **Docker Compose**. 
The production environment consists of the following isolated containers:

* `app`: PHP-FPM engine containing the Laravel core. Runs with reduced privileges (non-root user) for maximum security.
* `web`: **Nginx** web server (based on Alpine Linux). Acts as a reverse proxy, enforces redirection from HTTP to HTTPS, and manages SSL certificates.
* `db`: **MariaDB 10.11** database. Deliberately not exposed to the public network, listening exclusively on the loopback interface (`127.0.0.1`).
* `scheduler`: A containerized worker that continuously runs `php artisan schedule:work`, ensuring the execution of scheduled tasks without any dependency on the host operating system’s cron daemon.

## 🔒 Security

Security is a fundamental pillar of this project, implemented across multiple layers:

1. **Encrypted Traffic:** Native support for Let's Encrypt certificates via Certbot, ensuring end-to-end TLS (HTTPS) encryption.
2. **Database Isolation:** The MariaDB database rejects external connections. Access for maintenance or data inspection (e.g., via HeidiSQL/DBeaver) is possible only by establishing an encrypted **SSH tunnel** to the host server.
3. **Secure Sessions and Access:** Use of secure session drivers and adoption of the FIDO2 standard for access via Passkey, blocking phishing and brute-force attacks at the source.

## 🚀 Requirements and Installation

### Prerequisites
* Docker and Docker Compose installed on the host system.
* (Production only) A registered domain pointing to the server's IP address for generating SSL certificates.

## Environment Setup (Development / Production)

### 1. Clone the repository:
   ```bash
   git clone [https://github.com/tuo-username/nome-repo.git](https://github.com/tuo-username/nome-repo.git)
   cd nome-repo
   ```

### 2. Copy the environment configuration file and set the variables (including SMTP parameters for email):
   ```bash
   cp .env.example .env
   ```

### 3.  Launch in an Isolated Environment via Docker

The project is defined via code (`Infrastructure as Code`) using **Docker Compose**, ensuring absolute parity between the development environment and the production server (OVH VPS). The isolated containers include Nginx, PHP-FPM, MariaDB, and a dedicated Scheduler container.

**Docker Installation**
If Docker is not present on the system:
```bash
# (Example for Ubuntu/WSL)
sudo apt install docker.io docker-compose -y
sudo usermod -aG docker $USER
```

**Building and Starting the Infrastructure**
Navigate to the folder containing the `docker-compose.yaml` file (or `docker/`) and start the containers:
```bash
docker-compose up -d --build
```

**Commands Inside the Container**
To perform operations on Laravel (migrations, tests, tinker), “enter” the application container:
```bash
docker exec -it scadenze_app php artisan migrate
docker exec -it scadenze_app php artisan storage:link
docker exec -it scadenze_app php artisan tinker
```

### 4. Install PHP dependencies and run database migrations (OPTIONAL):
   ```bash
   docker exec -it scadenze_app composer install --no-dev --optimize-autoloader
   docker exec -it scadenze_app php artisan key:generate
   docker exec -it scadenze_app php artisan migrate --seed
   docker exec -it scadenze_app php artisan storage:link
   ```

## 📧 Email Notification Management (Testing)

Notifications are automatically handled by the `scheduler` container, which runs `php artisan schedule:work` in the background. 

To test email delivery (both locally and in production) and check the deadline queue, you can run the following command:
```bash
# If you're using the local PHP server:
php artisan app:send-deadline-reminders

# If you're using Docker:
docker exec -it scadenze_app php artisan app:send-deadline-reminders
```

## Note: If you're on WSL locally, use this command to manage authentication with the Passkey/Authenticator app:
```bash
# Start the server and make it accessible locally
php artisan serve --host=localhost
```

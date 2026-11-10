# MindHub

> **Student Counseling & Online Appointment System**

MindHub is a web-based guidance and counseling management platform designed to streamline mental health, academic, and personal consultation requests between students and counselors. It provides a secure, role-based portal where students can book and track appointments, and counselors can review requests and log post-session notes.

---

## 📌 Features

### 🎓 Student Portal
- **Account Registration & Authentication:** Secure signup and login with role-based session control and encrypted passwords.
- **Appointment Booking:** Schedule counseling sessions with custom categories (*Academic, Personal, Career, Mental Health, Other*), consultation modes (*Face-to-Face, Online*), and preferred time slots.
- **Status Tracking:** Real-time visibility into booking statuses (`Pending`, `Approved`, `Completed`, `Rejected`, `Cancelled`).

### 🩺 Counselor Portal
- **Request Management:** Review incoming student bookings with instant approval or rejection actions.
- **Session Notes & Records:** Document case summaries, actions taken, and follow-up requirements after each consultation.
- **Schedule Overview:** Filter and monitor pending, approved, and completed consultations.

### 🔒 Security & Architecture
- **Password Security:** Hashed using PHP's native `password_hash()` (Bcrypt).
- **SQL Injection Prevention:** All database queries utilize PDO prepared statements with bound parameters.
- **Session Management:** Secure role verification and strict cookie invalidation upon logout.

---

## 🛠️ Tech Stack

- **Backend:** PHP (Native / Vanilla, PDO)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Server Environment:** Apache / Nginx (XAMPP, WampServer, or native PHP CLI)

---

## 📁 Project Structure

```text
MindHub/
├── assets/
│   ├── css/
│   │   └── style.css          # Core application styling
│   ├── images/
│   └── js/
├── config/
│   └── db.php                 # PDO database connection setup
├── counselors/
│   ├── dashboard.php          # Counselor overview and request approvals
│   └── session-notes.php      # Documentation of completed sessions
├── includes/
│   ├── footer.php             # Reusable footer layout
│   └── header.php             # Reusable header and navigation
├── students/
│   ├── book.php               # Appointment scheduling form
│   └── dashboard.php          # Student consultation tracking
├── index.php                  # User login page
├── logout.php                 # Session termination and cookie clearance
├── mindhub.sql                # Database schema and table definitions
└── register.php               # Student account registration
```

---

## 🚀 Getting Started

### Prerequisites

Ensure you have the following installed on your machine:
- [PHP](https://www.php.net/) (version 7.4 or higher recommended)
- [MySQL](https://www.mysql.com/) / [MariaDB](https://mariadb.org/)
- A local server environment such as **XAMPP**, **WampServer**, or **Laragon**

---

### Installation & Setup

#### 1. Clone the Repository
```bash
git clone https://github.com/your-username/MindHub.git
cd MindHub
```

#### 2. Import the Database
1. Start your **Apache** and **MySQL** services in your control panel (e.g., XAMPP).
2. Open **phpMyAdmin** in your browser (`http://localhost/phpmyadmin`).
3. Click the **Import** tab.
4. Choose the `mindhub.sql` file located in the root of the project and click **Go**.
   *(This creates the `mindhub` database along with all required tables).*

#### 3. Configure Database Connection
Open `config/db.php` and verify that the database credentials match your local setup:

```php
$host     = "127.0.0.1";
$dbname   = "mindhub";
$username = "root";       // Your MySQL username
$password = "";           // Your MySQL password
```

#### 4. Run the Project
- If using **XAMPP/WAMP**: Move the project folder into your `htdocs` directory and navigate to:
  ```text
  http://localhost/MindHub/index.php
  ```
- Or run using the **PHP built-in server**:
  ```bash
  php -S localhost:8000
  ```
  Then visit `http://localhost:8000` in your web browser.

---

## 🗄️ Database Schema Summary

| Table | Description |
| :--- | :--- |
| `users` | Stores accounts, roles (`student`, `counselor`, `admin`), programs, and hashed credentials. |
| `counselor_schedules` | Tracks availability time blocks for counselors. |
| `appointments` | Contains booking requests, concerns, consultation modes, and status tracking. |
| `counseling_notes` | Houses post-consultation case summaries and follow-up flags. |
| `self_help_resources` | Repository for self-help and guidance reading materials. |

---

## 📄 License
This project is developed for educational and institutional counseling management purposes.
```
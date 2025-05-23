# Password Manager

A secure password management system built with PHP and MySQL.

## Features

- User registration and authentication
- Secure password storage using AES encryption
- Password generation with customizable parameters
- Website password management
- Session-based security

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone [your-repository-url]
```

2. Create a MySQL database and import the schema:
```bash
mysql -u your_username -p your_database < database/schema.sql
```

3. Configure the database connection in `config/database.php`

4. Start the PHP development server:
```bash
php -S localhost:8000
```

## Usage

1. Register a new account
2. Log in with your credentials
3. Generate or save passwords for different websites
4. View and manage your saved passwords

## Security Features

- Passwords are encrypted using AES-256-CBC
- Each user has a unique encryption key
- Session-based authentication
- SQL injection prevention using PDO
- XSS prevention using htmlspecialchars

## Project Structure

```
├── classes/
│   ├── User.php
│   ├── PasswordGenerator.php
│   └── PasswordManager.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── index.php
├── logout.php
└── README.md
```

## License

This project is licensed under the MIT License. 
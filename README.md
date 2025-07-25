# ERP Dashboard Setup Guide

## Prerequisites

- **PHP** (recommended: PHP 8+)
- **Composer**
- **Node.js** & **npm**

> **Note:**  
> Make sure PHP, Composer, and Node.js are installed on your device.  
> If not, download and install them from their official websites.

---

## Installation Steps

### 1. Clone the Repository

```sh
git clone <repository-url>
cd erp-dashboard
```

### 2. Install PHP Dependencies

```sh
composer install
```

### 3. Install Node.js Dependencies

```sh
npm install
```

### 4. Install DomPDF Package

```sh
composer require barryvdh/laravel-dompdf
```
uncomment untuk bisa mengekstak gambar 
file php.ini 
;extension=gd -> extension=gd
### 5. Configure Environment

- Copy `.env.example` to `.env` if not already present:
  ```sh
  cp .env.example .env
  ```
- Set up your database credentials in the `.env` file.

### 6. Run Database Migrations

```sh
php artisan migrate
```

### 7. Create Your First User

You can use Laravel Tinker to create a user:

```sh
php artisan tinker
```
Then, run the following in the Tinker shell:
```php
$user = new App\Models\User;
$user->name = 'manager';
$user->email = 'manager@gmail.com';
$user->password = bcrypt ('123');
$user->save();
```

### 8. Generate Application Key

```sh
php artisan key:generate
```

### 9. Serve the Application

```sh
php artisan serve
```
### Untuk aktivkan seeder
php artisan migrate:refresh --seed 
---

Your project is now ready!  
Open [http://localhost:8000](http://localhost:8000) in your browser to access the ERP Dashboard.



#sampah
railway.todml
[build.nixpacksConfigPath]
nixpacks.toml = "nixpacks.toml"











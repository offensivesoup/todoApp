# porting manual

## DEV

#### feat. XAMPP 8.2.12

| Part         | Version          |
|--------------|------------------|
| OS           | Windows 11       |
| PHP          | 8.2.12           |
| Composer     | 2.8.9            |
| Laravel      | 10.3.3           |
| MariaDB      | 10.4.32          |

---

## install

```bash
git clone https://github.com/offensivesoup/todoApp.git
composer install
```

## env

```bash
cp .env.example .env 

need correction

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## migrate

```bash
php artisan key:generate
php artisan migrate
```

## clear cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## start
```bash
php artisan serve
```
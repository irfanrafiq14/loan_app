# MaxWallet

MaxWallet is a Laravel 12 loan management app with a secure admin panel and a mobile-first customer experience. Admins create customers with an app name, assign loans, and review payment screenshots. Customers open the app URL and sign in with phone plus OTP.

## Stack

- Laravel 12 / PHP 8.2+
- MySQL
- Blade + Tailwind CSS + Alpine.js
- PHPUnit feature tests

Live server steps are in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Local installation

1. Copy the environment file and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```

2. Create a MySQL database named `maxwallet` (XAMPP example):

```sql
CREATE DATABASE maxwallet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Set these values in `.env`:

```env
APP_NAME=MaxWallet
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maxwallet
DB_USERNAME=root
DB_PASSWORD=
```

4. Install PHP and frontend dependencies:

```bash
composer install
npm install
npm run build
```

5. Run migrations and demo data:

```bash
php artisan migrate:fresh --seed
```

6. Start the app:

```bash
php artisan serve
```

Then open [http://127.0.0.1:8000](http://127.0.0.1:8000).

XAMPP users can also point Apache at `public/` (`http://localhost/loan_app/public`) after `npm run build`. Set `APP_URL` to that same public URL.

## Demo accounts

Admin:

- URL: `/admin/login`
- Email: `admin@maxwallet.test`
- Password: `password`

Customers do not use a password. From the admin panel, create a customer and set their **app name**. Copy the base app URL from the customer page (nothing extra — no `?app=` query) and send it to them. They open that URL, sign in with phone and OTP, and then their app name is shown.

OTP is a local demo flow: no SMS is sent. The verification screen shows a 59-second timer, then auto-fetches and submits the code after about 3–4 seconds.

## Main routes

Customer:

- `/`
- `/verify-otp`
- `/home`
- `/orders`
- `/profile`
- `/loans/{loan}/pay`
- `/payments`
- `/logout`

Admin:

- `/admin/login`
- `/admin/dashboard`
- `/admin/customers`
- `/admin/loans`
- `/admin/payments`
- `/admin/payment-link`
- `/admin/support-email`

## Security notes

- Customers sign in with a registered phone number and a hashed OTP. No special login-link tokens are issued.
- OTP values are hashed in the database. A short-lived demo code is kept in the session only so the UI can auto-fill it.
- Payment screenshots are stored on the private `local` disk (`storage/app/private`). Customers cannot browse file paths. Admins view screenshots through an authorized route.
- Customers can only see and pay their own loans. Policies block ID tampering in the URL.
- Payment status can be changed only by an admin, inside a database transaction.

## Tests

```bash
php artisan test
```

The suite covers admin login, customer OTP login, unauthorized loan access, payment submission, screenshot validation, admin approve/reject, and logout.

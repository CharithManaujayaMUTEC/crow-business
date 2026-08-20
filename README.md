# Crow Business Admin Starter

Laravel 13 + Filament 5 starter for the Crow.lk internal business administration flow.

## Scope

Customer -> Product/Service -> Quotation -> Accept/Reject -> Invoice -> Payment -> Recurring Service -> Automated reminders -> SMS logs.

Also includes Expenses, Employees/Payroll structure, SMS settings, document numbering and a basic dashboard.

This ZIP intentionally does NOT include `vendor/` or `node_modules/`. Start from a fresh Laravel 13 application, install Filament 5, then copy the `app/` and `database/` folders from this ZIP into the Laravel project.

## Important

- No Lead module. Everyone is a Customer.
- Quotation number: `QUT-YYYYMM-NNN`
- Invoice number: `INV-YYYYMM-NNN`
- Existing Notify.lk SMS settings can be mapped to the included `sms_settings` table.
- The SMS adapter is isolated in `app/Services/Sms/NotifySmsService.php`.
- This is an MVP foundation; review business/tax/payroll rules before production use.

## Install

```bash
composer create-project laravel/laravel crow-business "^13.0"
cd crow-business

composer require filament/filament:"^5.0"
php artisan filament:install --panels
php artisan make:filament-user

# Copy this ZIP's app/ and database/ contents into the project.

php artisan migrate
php artisan db:seed --class=CrowDemoSeeder

npm install
npm run build

php artisan serve
```

Open `/admin`.

For Windows PowerShell, Filament recommends the `~5.0` constraint if `^` is interpreted by the shell.

## Queue / scheduler

For development:

```bash
php artisan queue:work
php artisan schedule:work
```

For production, configure a queue worker and cron:

```cron
* * * * * cd /path/to/crow-business && php artisan schedule:run >> /dev/null 2>&1
```

## Notify.lk

Set these values in `.env`:

```env
SMS_ENABLED=false
NOTIFYLK_API_URL=https://app.notify.lk/api/v1/send
NOTIFYLK_USER_ID=
NOTIFYLK_API_KEY=
NOTIFYLK_SENDER_ID=Crow.lk
SMS_COUNTRY_CODE=94
```

The Filament `SMS Settings` resource can also store/update the credentials in the database. For production, consider encrypting the API key.

## Production

After deployment:

```bash
php artisan migrate --force
php artisan optimize
php artisan filament:optimize
```

Do not commit real API keys or customer data.

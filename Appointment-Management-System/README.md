# Appointment Management System

Full-stack Laravel 10 project for managing medical appointments, including medical profiles, online/onsite visits, notifications, and payments.

## ERD Source

- `Files/ERD/ERD.dbml`

Generated from the ERD:

- Migrations: `database/migrations`
- Models: `app/Models`
- Enums: `app/Enums`

## Setup (Local)

1. `composer install`
2. Configure `.env` (DB credentials + app URL)
3. `php artisan migrate`

## Notes

- This project uses a custom `notifications` table/model from the ERD (not Laravel’s default database notifications schema).

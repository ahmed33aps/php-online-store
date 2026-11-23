# PHP online store

A PHP/MySQL learning project with the original HTML/CSS frontend and separate customer, administrator and delivery-agent pages.

## Features

- Customer registration, login, profile editing and order creation.
- Product/category management and customer/order views for administrators.
- Delivery status updates for agents.
- Inventory adjustments and order-status history.

## Requirements

PHP 8.1+ with `mysqli` and MySQL 8.0+. This repository was checked locally using PHP 8.1 and MySQL 8.0. The PHP development server is for local development only.

## Local setup

1. Create a local database using `schema.sql`. This schema was reconstructed from the supplied PHP queries. It is not the missing original database and includes no customer records.
2. Create a dedicated local MySQL user with access only to this development database.
3. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` and `DB_PASSWORD` in your shell/environment. Do not commit real values. `.env` files are ignored, but this application does not automatically load them.
4. Run `php -S 127.0.0.1:8080` from this directory and open `http://127.0.0.1:8080`.
5. Public registration creates customers only. Create the first administrator locally using `php create_admin.php USERNAME EMAIL < password-file`, where the input file is private and contains a password of at least 12 characters. Delete that local file afterward. Never place it inside this repository or pass a password on the command line.
6. An administrator can create a category and products. Register another customer and promote them to delivery agent from the administrator page when needed. Existing order history is preserved.

## Repairs

- Removed embedded admin signup secret and public administrator registration.
- Removed plaintext-password storage/display and unnecessary SSN collection.
- Added shared role checks, request tokens and safer session settings.
- Parameterized login and order input, validated positive quantities, and added transactions/row locks around inventory-changing customer actions.
- Rejects customer access to other customers' orders and derives agent status transitions from stored order state.
- Uses consistent lower-case table names on case-sensitive MySQL installations.
- Preserved the original layout, gradient and page flows, while removing an external placeholder background and fixing inline action-form spacing.

## Validation and limits

43 local integration checks passed for authentication, role separation, CSRF rejection, order ownership, stock handling, checkout/status changes and representative page rendering. All PHP files passed syntax checks. Actual administrator/product pages were visually inspected after the style repair.

This is a repaired portfolio demo, not a security audit or production-ready commerce service. No real payment processing was supplied or added. `payments`, `cash` and `card` are legacy schema stubs only and must never store payment-card details. Payment integration, password reset/email verification, login rate limiting, operational monitoring, complete accessibility review and deployment hardening remain outside this work. No external deployment was performed.

No new license is assigned. Review authorship and rights before making this repository public.

# GWTTT - Guild Wars Titles & Treasures Tracker

GWTTT tracks Guild Wars accounts, characters, title progress, Nightfall treasure drops and collection reminders, Hall of Monuments miniature dedication and inventory, and sixth-year everlasting tonics without juggling separate spreadsheets or a personal wiki.

Installation instructions are available in [docs/INSTALL.md](docs/INSTALL.md). Optional Apache and Cloudflare defense-in-depth guidance is available in [docs/OPTIONAL-WEB-HARDENING.md](docs/OPTIONAL-WEB-HARDENING.md).

## Requirements

GWTTT is operating-system agnostic and does not require Apache specifically. The current code requires:

- **PHP 8.3 or newer** with the **MySQLi** extension enabled
- A web server capable of serving PHP applications, such as Apache or nginx with PHP-FPM
- **MariaDB 10.11 or newer**, or a compatible MySQL server
- **Composer** for PHP dependencies such as PHPMailer
- A modern web browser
- A scheduler such as cron if automated reminder e-mails are desired

The current production installation runs on PHP 8.3 and MariaDB 10.11. PostgreSQL is not currently supported; database access is written for MySQL/MariaDB using MySQLi.

## Current capabilities

GWTTT currently supports user registration and authentication, password changes and password reset, e-mail changes, multiple Guild Wars accounts and characters, account and character title tracking, automatic title relationships such as Kind of a Big Deal, Nightfall treasure tracking and reminders, miniature/Hall of Monuments tracking, sixth-year everlasting tonic inventory, administrator title/rank and miniature catalog management, and configurable SMTP e-mail delivery.

### Optional Guild Wars title import

If you use [GWToolbox++](https://www.gwtoolbox.com/), the optional [GWTTT Titles Exporter plugin](https://github.com/mauirixxx/GWTTT-Account-Exporter) can read your title progress directly from the running Guild Wars client and export it to a JSON file for easy import into GWTTT. The export includes both character-specific and account-wide title progress, reducing the need to enter or update title values by hand.

[Download the latest GWTTT Titles Exporter release](https://github.com/mauirixxx/GWTTT-Account-Exporter/releases/latest). GWToolbox++ is only required for using the exporter plugin; it is **not** required to use GWTTT itself. See the exporter repository for plugin installation and usage instructions.

A fresh installation uses the single consolidated database initializer at `sql-init/gwttt-initialization.sql`.

## Documentation

- [Installation](docs/INSTALL.md)
- [Optional Web Server and Cloudflare Hardening](docs/OPTIONAL-WEB-HARDENING.md)

## Future ideas

- Upload a picture of your character
- ???
- Profit!! 😁

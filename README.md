<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Runtime and dependency maintenance

Use PHP 8.5.12 or a newer 8.5 patch release (`~8.5.12` in Composer), and Node.js 22.12 or newer. The production Docker image pins PHP 8.5.12 by digest. PHP 8.5 includes DOM and OPcache; the image checks required extensions after installing the remaining extensions.

Install dependencies with `composer install` and `npm ci`. npm lifecycle scripts are disabled in `.npmrc`; explicit commands such as `npm run build` still work.

Check dependencies before a release:

```bash
composer validate --strict
composer check-platform-reqs
composer audit --locked
npm audit
npm audit --omit=dev
npm run build
php vendor/phpunit/phpunit/phpunit
```

Prefer patched compatible versions. If a vulnerable package has no compatible fix, remove it and its integration together, then document the affected feature and verify the remaining application. Disabling install scripts alone does not disable vulnerable package code.

## Docker

Build the production image:

```bash
docker build -t britt-profile .
```

Generate an application key, then place the result in a local `.env.docker` file:

```bash
docker run --rm britt-profile php artisan key:generate --show
```

```dotenv
APP_NAME="Britt Portfolio"
APP_ENV=production
APP_KEY=base64:replace-with-generated-key
APP_DEBUG=false
APP_URL=http://localhost:8080
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/storage/database.sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
PORTFOLIO_ADMIN_TOKEN=replace-with-a-long-random-secret
```

Start the portfolio with persistent database and media storage:

```bash
docker run -d --name britt-profile -p 8080:80 --env-file .env.docker -v profile-storage:/var/www/html/storage britt-profile
```

The application is then available at `http://localhost:8080` once its database has been provisioned. The image does not automatically run migrations, seeders, queues, or scheduled jobs during startup. Review pending migrations and back up the maintained database before applying them; do not use seeders as a deployment or synchronization shortcut.

## Publishing portfolio content

Public profile content is maintained in [`resources/data/portfolio-content.json`](resources/data/portfolio-content.json), including projects, work experience, education, research, recognition, badges and certificates. The Services page uses [`config/service_catalog.php`](config/service_catalog.php). The public site reads the repository content, rather than the corresponding admin database records. Public media referenced by the snapshot is bundled under [`public/repository-media/`](public/repository-media/) where available; other public assets are in `public/`. Keep asset paths and files together when making changes.

To update the site, edit the relevant versioned content and public assets locally, validate the content and links, run the applicable tests and build, then commit and deploy the reviewed changes through the existing Git and Northflank workflow. Check the deployed pages and media after deployment. Review credential records against their issuing sources and preserve the certificate verification-link policy; do not commit private certificate documents, personal data or secrets.

New Credly badges and certificates do **not** automatically appear on the public site. Add or update their verified public details and permitted verification links in the repository snapshot, include any required public images, and deploy the changes. Admin editing and admin badge/certificate sync for versioned content are disabled; the old sync commands update database records and do not publish the snapshot.

GitHub contributions remain live cached data, and private inquiries are processed dynamically through the configured mail transport. A delivery-capable mail service must be configured for actual email delivery. Keep mail credentials in the deployment's secret configuration, never in the repository. The admin GitHub sync remains available for an immediate refresh.

Laravel declares a GitHub contribution refresh hourly at minute 20. This is a schedule declaration, not evidence that a worker is running. If scheduled refresh is desired, configure the hosting scheduler to invoke Laravel's `schedule:run` each minute. Docker startup does not start the scheduler or queue workers.


## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

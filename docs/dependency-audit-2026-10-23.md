# Dependency maintenance - 23 October 2026

## PHP

The checkout already required PHP `~8.5.12` and pinned the production image to `php:8.5.12-apache-bookworm` by SHA-256 digest when this maintenance started. These existing changes were preserved. The local CLI was verified as PHP 8.5.12; Composer's platform requirements passed.

PHP 8.5.12 is a security release: [official release announcement](https://www.php.net/releases/8_5_12.php). The Docker build checks the PHP version and required extensions. Composer permits later 8.5 patch versions; the pinned Docker image must be updated separately when adopting a later release.

## Package changes

All direct npm packages now declare the currently installed release as their minimum compatible version:

| Package | Previous manifest | Updated manifest |
| --- | --- | --- |
| @inertiajs/vue3 | ^3.6.1 | ^3.9.1 |
| @lucide/vue | ^1.24.0 | ^1.61.0 |
| @vitejs/plugin-vue | ^6.0.8 | ^6.0.10 |
| vue | ^3.5.39 | ^3.5.44 |
| concurrently | ^9.0.1 | ^10.0.6 |
| laravel-vite-plugin | ^3.1 | ^3.2.0 |
| vite | ^8.0.0 | ^8.3.5 |

The installed application dependencies were already at these versions. The installed development process runner, concurrently, changed from 9.2.5 to 10.0.6, with its transitive dependencies refreshed. `npm outdated` returned no remaining direct npm updates.

A Composer update dry run found no changes within the existing constraints. PHPUnit remains on the project's constraint `^12.5.12` (locked to 12.5.40); PHPUnit 13 is a separate major migration and was not required to resolve an advisory.

## Disabled packages and functionality

No packages were disabled or removed to mitigate an advisory: both initial and final npm and Composer audits reported zero known vulnerabilities. No portfolio features were removed.

The concurrently upgrade affects the local `composer dev` process launcher. Its Node minimum is 22 and it is ESM-only; the existing integration uses its CLI. Vite requires Node 22.12 or newer on that release line, so the repository now declares Node `>=22.12.0`. The installed Node 22.17.1 and the Docker Node 22 image satisfy this requirement. See the [upstream concurrently 10 release notes](https://github.com/open-cli-tools/concurrently/releases/tag/v10.0.0).

The existing npm `ignore-scripts=true` setting remains enabled. It prevents automatic dependency lifecycle scripts; it does not disable imported package code. Explicit build commands still run.

## Verification

| Check | Result |
| --- | --- |
| Full npm audit, before and after | 0 vulnerabilities |
| Production npm audit after update | 0 vulnerabilities |
| Composer locked audit | 0 advisories, 0 abandoned packages |
| Composer validation and platform requirements | Passed |
| Clean npm install from lockfile, lifecycle scripts disabled | Passed |
| Production Vite build, before and after | Passed |
| PHP test suite, before and after | 40 passed, 844 assertions |
| concurrently CLI success, names, colors, shutdown flags, failure propagation | Passed smoke checks |
| Git whitespace check | Passed |

Composer 2.8.10 emits existing PHP 8.5 deprecation notices from its own bundled dependencies; validation and audits still succeed. The Composer executable was not changed.

The container was not rebuilt because Docker is unavailable in this environment. No deployment, remote push, shared PHP configuration change, or application database migration was performed. Audit results reflect the advisory databases available on the review date and do not establish the absence of undisclosed vulnerabilities.

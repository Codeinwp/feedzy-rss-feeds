# Agent workflow

## Project Overview

Feedzy RSS Feeds is a WordPress plugin (lite/free version) for RSS aggregation, content curation, and autoblogging. It supports importing RSS feeds as WordPress posts, displaying feeds via shortcodes/blocks/widgets, and integrates with Elementor. A separate Pro plugin extends its functionality.

- **Text domain:** `feedzy-rss-feeds`
- **Min PHP:** 7.2 | **Min WP:** 6.0
- **Main bootstrap:** `feedzy-rss-feed.php` (defines constants, registers autoloader, runs plugin)

## Commands

### Build & Dev (JS/Blocks)
```bash
npm install                  # Install JS dependencies
npm run build                # Production build all blocks/JS bundles
npm run dev                  # Watch mode for all blocks (parallel)
npm run build:block          # Build just the Gutenberg block
npm run build:loop           # Build just the Loop block
```

### PHP Linting & Static Analysis
```bash
composer install             # Install PHP dependencies
composer run lint            # Run PHPCS (WordPress Coding Standards)
composer run format          # Auto-fix PHPCS issues
composer run phpstan         # Run PHPStan (level 6, includes/ only)
```

### PHPUnit Tests
```bash
# Requires WordPress test suite (MySQL service needed)
bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1:3306
phpunit                              # Run all unit tests
phpunit tests/test-plugin.php        # Run a single test file
```

### E2E Tests (Playwright + wp-env)
```bash
npm run wp-env start         # Start Docker-based WordPress environment
npm run test:e2e             # Run Playwright E2E tests
npm run test:e2e:debug       # Run E2E with Playwright UI mode
```

### Other
```bash
npm run grunt                # Run Grunt tasks (readme.txt → readme.md conversion, version bumping)
npm run dist                 # Build dist package (bin/dist.sh)
npm run lint:js              # Lint JS files via wp-scripts
```

## Architecture

### Autoloading Convention
The plugin uses a custom `spl_autoload_register` in the bootstrap file. Classes prefixed with `Feedzy_Rss_Feeds` are resolved by converting underscores to hyphens and lowering the case, then searching these directories in order:
1. `includes/`
2. `includes/abstract/`
3. `includes/admin/`
4. `includes/gutenberg/`
5. `includes/util/`
6. `includes/elementor/`

Example: `Feedzy_Rss_Feeds_Admin` → `includes/admin/feedzy-rss-feeds-admin.php`

### Core Class Hierarchy
- **`Feedzy_Rss_Feeds`** (`includes/feedzy-rss-feeds.php`) — Singleton core class. Loads dependencies and registers all hooks via the Loader pattern.
- **`Feedzy_Rss_Feeds_Loader`** (`includes/feedzy-rss-feeds-loader.php`) — Central hook registration (actions/filters stored in arrays, executed via `run()`).
- **`Feedzy_Rss_Feeds_Admin_Abstract`** (`includes/abstract/`) — Base class for admin functionality including feed fetching, shortcode rendering, and image handling.
- **`Feedzy_Rss_Feeds_Admin`** (`includes/admin/feedzy-rss-feeds-admin.php`) — Extends abstract. Handles admin UI, post types (`feedzy_categories`, `feedzy_imports`), REST routes, settings, shortcode `[feedzy-rss]`.
- **`Feedzy_Rss_Feeds_Import`** (`includes/admin/feedzy-rss-feeds-import.php`) — Feed-to-post import engine. Manages cron jobs, import post type metaboxes, magic tags.

### Custom Post Types
- `feedzy_categories` — Groups of feed URLs for reuse.
- `feedzy_imports` — Import job configurations (feed source → WordPress posts).

### JavaScript / Block Architecture
Blocks are built with `@wordpress/scripts`. Each has its own entry point under `js/`:
- `js/FeedzyBlock/` → `build/block/` — Main Gutenberg block for displaying feeds
- `js/FeedzyLoop/` → `build/loop/` — Loop block variant
- `js/Onboarding/` → `build/onboarding/` — Setup wizard
- `js/ActionPopup/` → `build/action-popup/` — Action chain popup
- `js/Conditions/` → `build/conditions/` — Import filter conditions UI
- `js/FeedBack/` → `build/feedback/` — Feedback prompt
- `js/Review/` → `build/review/` — Review prompt

Legacy JS files (non-bundled) in `js/` root: TinyMCE integration, Elementor widget, lazy loading, settings, categories.

### Pro Plugin Integration
The lite plugin checks for Pro via `feedzy_is_pro()` and `FEEDZY_PRO_BASEFILE` / `FEEDZY_PRO_ABSPATH` constants. Many hooks have `feedzy_` prefixed filters that Pro extends. The import feature conditionally loads based on `feedzy_is_pro(false)` or `has_filter('feedzy_free_has_import')`.

### Key WordPress Hooks
- Shortcode: `[feedzy-rss]` registered in `define_admin_hooks()`
- REST API: routes registered via `rest_route` on `rest_api_init`
- Cron: `feedzy_cron` action drives scheduled imports
- Logging: `feedzy_log` action → `Feedzy_Rss_Feeds_Log` class

### Tests
- **PHPUnit tests:** `tests/test-*.php` files (prefixed with `test-`), bootstrapped by `tests/bootstrap.php`. Require WP test suite installation.
- **E2E tests:** `tests/e2e/specs/*.spec.js` using Playwright with `@wordpress/e2e-test-utils-playwright`. Run against `wp-env` Docker environment.
- **PHPStan stubs:** `tests/php/static-analysis-stubs/` provides type stubs for static analysis.

## Coding Standards

- Follows WordPress Coding Standards enforced via PHPCS (`phpcs.xml`)
- WordPress-VIP-Go rules included
- PHPStan level 6 for `includes/` directory
- JS linting via `@wordpress/eslint-plugin`

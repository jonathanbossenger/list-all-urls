# GitHub Copilot Instructions for List all URLs

This is a WordPress plugin that generates a list of all published post URLs in a WordPress site.

## Project Overview

**Plugin Name:** List all URLs  
**Purpose:** Creates an admin page under Settings > List All URLs that outputs an ordered list of all published URLs from posts, pages, and custom post types.  
**WordPress Version:** Tested up to 6.8.3  
**PHP Version:** Requires PHP 8.0 or higher  
**License:** GPL v2 or later

## Development Guidelines

### WordPress Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) for all PHP code
- Use WordPress PHP Coding Standards (WPCS) version 3.0 for linting
- Run `composer install` to set up PHP_CodeSniffer with WPCS
- Lint code with: `./vendor/bin/phpcs --standard=WordPress list-all-urls.php`

### Code Structure

- **Main plugin file:** `list-all-urls.php` - Contains all plugin functionality
- **Functions prefix:** All functions use the `jb_lau_` prefix to avoid naming conflicts
- **Text domain:** `list-all-urls` for internationalization

### Key Functions

- `jb_lau_get_all_post_types()` - Fetches all custom post types
- `jb_lau_generate_url_list()` - Generates list of URLs based on provided arguments
- `jb_lau_get_posts()` - Fetches posts based on provided arguments
- `jb_lau_plugin_menu()` - Adds plugin menu to WordPress admin dashboard
- `jb_lau_render_admin_page()` - Renders the admin page interface

### WordPress Best Practices

1. **Security:**
   - Always check user capabilities with `current_user_can()`
   - Sanitize user input with `sanitize_text_field()` and similar functions
   - Use nonces for form submissions to prevent CSRF attacks
   - Escape output with `esc_html()`, `esc_url()`, `esc_attr()`, etc.

2. **Data Handling:**
   - Use WordPress core functions like `get_posts()`, `get_permalink()`, `wp_parse_args()`
   - Never directly access database; use WordPress APIs
   - Check if `ABSPATH` is defined to prevent direct file access

3. **Hooks and Actions:**
   - Use `add_action()` and `add_filter()` for WordPress hooks
   - Current hooks: `admin_menu` for adding settings page

4. **Internationalization:**
   - Use `__()` and `_e()` for translatable strings
   - Always include text domain: `list-all-urls`

### Code Modifications

When making changes:
- Maintain backwards compatibility with WordPress 6.8.3 and PHP 8.0+
- Keep the single-file plugin structure unless there's a compelling reason to expand
- Follow existing naming conventions (function prefix: `jb_lau_`)
- Update version number and changelog in plugin header if making substantial changes
- Add inline documentation for new functions using PHPDoc format

### Testing

- No automated test suite is currently configured
- Manual testing should be done in a WordPress environment
- Test with different post types: posts, pages, and custom post types
- Verify URL generation with and without the "make links clickable" option

### Build Process

- Run `composer install` to install development dependencies (WPCS)
- No build or compilation step required (pure PHP plugin)
- No JavaScript bundling or CSS preprocessing

### Common Tasks

**Linting:**
```bash
composer install
./vendor/bin/phpcs --standard=WordPress list-all-urls.php
```

**Auto-fixing code style:**
```bash
./vendor/bin/phpcbf --standard=WordPress list-all-urls.php
```

## Additional Context

- Plugin is designed to be lightweight and simple
- Admin interface uses native WordPress form elements
- No external dependencies required for plugin functionality
- Development dependencies are only for code quality (WPCS)

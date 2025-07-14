# Laravel DevLogger Dashboard
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)

A beautiful, feature-rich dashboard for managing Laravel DevLogger records with IDE integration and advanced filtering capabilities.

## Features

- **Stunning UI** - Modern, responsive design with dark mode support
- **Advanced Search & Filtering** - Search by message, exception class, file path, level, status, and date range
- **IDE Integration** - Click to open files directly in your IDE (VS Code, PhpStorm, Sublime, Atom)
- **Real-time Statistics** - Dashboard overview with log counts and status summaries
- **Secure Access** - Environment-based access control with authentication and authorization
- **Live Updates** - Built with Laravel Livewire for reactive user experience
- **Mobile Responsive** - Works perfectly on all device sizes
- **Dark Mode** - Beautiful dark theme with automatic persistence

## Requirements

- PHP 8.1+
- Laravel 10.0+, 11.0+ or 12.0+
- Laravel Livewire 3.0+
- `onamfc/laravel-devlogger` package

## Installation

1. Install the package via Composer:

```bash
composer require onamfc/laravel-devlogger-dashboard
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag=devlogger-dashboard-config
```

3. (Optional) Publish the views for customization:

```bash
php artisan vendor:publish --tag=devlogger-dashboard-views
```

4. (Optional) Publish the assets:

```bash
php artisan vendor:publish --tag=devlogger-dashboard-assets
```

## Configuration

The package configuration is located at `config/devlogger-dashboard.php`. Key configuration options include:

### Environment Access Control

```php
'allowed_environments' => [
    'local',
    'staging',
    'development'
],
```

### Authentication & Authorization

```php
'require_auth' => env('DEVLOGGER_DASHBOARD_AUTH', true),
'authorization_gate' => env('DEVLOGGER_DASHBOARD_GATE', null),
'allowed_ips' => env('DEVLOGGER_DASHBOARD_IPS') ? explode(',', env('DEVLOGGER_DASHBOARD_IPS')) : [],
```

### IDE Integration

```php
'ide' => [
    'default' => env('DEVLOGGER_IDE', 'vscode'),
    'handlers' => [
        'vscode' => 'vscode://file/{file}:{line}',
        'phpstorm' => 'phpstorm://open?file={file}&line={line}',
        'sublime' => 'subl://open?url=file://{file}&line={line}',
        'atom' => 'atom://core/open/file?filename={file}&line={line}',
    ]
],
```

## Environment Variables

Add these to your `.env` file:

```env
# Dashboard Configuration
DEVLOGGER_DASHBOARD_PREFIX=devlogger
DEVLOGGER_DASHBOARD_AUTH=true
DEVLOGGER_DASHBOARD_GATE=null
DEVLOGGER_DASHBOARD_IPS=

# IDE Configuration
DEVLOGGER_IDE=vscode
DEVLOGGER_BASE_PATH=/path/to/your/project
DEVLOGGER_SHOW_FILE_PREVIEW=true

# UI Configuration
DEVLOGGER_THEME=dark
DEVLOGGER_BRAND_NAME="DevLogger Dashboard"
DEVLOGGER_AUTO_REFRESH=false
```

## Usage

### Accessing the Dashboard

Once installed, visit `/devlogger` in your browser (or your configured route prefix). The dashboard will only be accessible in allowed environments and to authenticated users.

### IDE Integration Setup

#### VS Code
1. Install the "Open in Application" extension or similar
2. The dashboard will generate `vscode://file/path/to/file:line` URLs
3. Click any file path in the dashboard to open it directly in VS Code

#### PhpStorm
1. Enable "Remote call" in PhpStorm settings
2. The dashboard will generate `phpstorm://open?file=path&line=number` URLs
3. Click any file path to open it in PhpStorm

#### Other IDEs
Configure your IDE to handle custom URL schemes, or use the "Copy Path" button as a fallback.

### Authorization Gates

You can create a custom authorization gate to control dashboard access:

```php
// In your AuthServiceProvider
Gate::define('access-devlogger-dashboard', function ($user) {
    return $user->hasRole('developer') || $user->email === 'admin@example.com';
});
```

Then set in your `.env`:
```env
DEVLOGGER_DASHBOARD_GATE=access-devlogger-dashboard
```

### Custom Middleware

The package includes `DevLoggerDashboardMiddleware` which handles:
- Environment checking
- IP allowlisting
- Authentication verification
- Authorization gate checking

You can extend or replace this middleware by binding your own implementation.

## Features Overview

### Dashboard Statistics
- Total log count
- Today's logs
- Error and warning counts
- Open vs resolved status counts

### Advanced Filtering
- Search across message, exception class, file path, and request URL
- Filter by log level (emergency, alert, critical, error, warning, notice, info, debug)
- Filter by status (open, resolved)
- Date range filtering
- Sortable columns

### Log Management
- View detailed log information
- Mark logs as resolved or reopen them
- Delete individual logs or bulk delete
- Copy file paths to clipboard
- Open files directly in your IDE

### File Preview
- View code context around the error line
- Syntax highlighting for better readability
- Highlighted target line where the error occurred

## Security Considerations

- **Environment Restriction**: Only accessible in configured environments
- **Authentication Required**: Users must be logged in (configurable)
- **Authorization Gates**: Optional fine-grained access control
- **IP Allowlisting**: Optional IP-based access restriction
- **No Public Access**: Designed specifically for development environments

## Customization

### Views
Publish the views to customize the UI:

```bash
php artisan vendor:publish --tag=devlogger-dashboard-views
```

Views will be published to `resources/views/vendor/devlogger-dashboard/`.

### Styling
The dashboard uses Tailwind CSS with a custom configuration. You can override styles by publishing the views and modifying the templates.

### Adding Custom Actions
You can extend the Livewire components to add custom functionality:

```php
// Create your own component extending the base
class CustomLogDashboard extends \Onamfc\DevLoggerDashboard\Http\Livewire\LogDashboard
{
    public function customAction()
    {
        // Your custom logic here
    }
}
```

## API Reference

### Services

#### IdeService
Handles IDE URL generation:

```php
$ideService = app(\Onamfc\DevLoggerDashboard\Services\IdeService::class);
$url = $ideService->generateIdeUrl('/path/to/file.php', 123);
```

#### FileService
Handles file preview generation:

```php
$fileService = app(\Onamfc\DevLoggerDashboard\Services\FileService::class);
$preview = $fileService->getFilePreview('/path/to/file.php', 123, 10);
```

## Troubleshooting

### Dashboard Not Accessible
1. Check that you're in an allowed environment
2. Verify authentication is working
3. Check IP allowlist configuration
4. Ensure the route prefix is correct

### IDE Links Not Working
1. Verify your IDE supports custom URL schemes
2. Check the IDE configuration in the config file
3. Ensure file paths are absolute
4. Try the "Copy Path" fallback option

### File Previews Not Showing
1. Check file permissions
2. Verify the base path configuration
3. Ensure files exist at the specified paths
4. Check the `show_preview` configuration

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

If you encounter any issues or have questions, please open an issue on the GitHub repository.
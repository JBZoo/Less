# JBZoo / Less

[![CI](https://github.com/JBZoo/Less/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Less/actions/workflows/main.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Less/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Less?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Less/coverage.svg)](https://shepherd.dev/github/JBZoo/Less)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Less/level.svg)](https://shepherd.dev/github/JBZoo/Less)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/less/badge)](https://www.codefactor.io/repository/github/jbzoo/less/issues)
[![Stable Version](https://poser.pugx.org/jbzoo/less/version)](https://packagist.org/packages/jbzoo/less/)    [![Total Downloads](https://poser.pugx.org/jbzoo/less/downloads)](https://packagist.org/packages/jbzoo/less/stats)    [![Dependents](https://poser.pugx.org/jbzoo/less/dependents)](https://packagist.org/packages/jbzoo/less/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/less)](https://github.com/JBZoo/Less/blob/master/LICENSE)


A powerful PHP wrapper for [wikimedia/less.php](https://github.com/wikimedia/less.php) that provides enhanced LESS compilation with caching, advanced configuration options, and streamlined error handling.

## Features

- **Smart Caching**: Automatic file-based caching with TTL support
- **Flexible Configuration**: Comprehensive options for paths, variables, and compilation behavior
- **Global Variables**: Define LESS variables available across all compiled files
- **Auto-loading**: Automatically include mixin files before compilation
- **Custom Functions**: Register custom PHP functions for use in LESS files
- **Import Path Management**: Configure multiple import directories with URL mappings
- **Enhanced Error Handling**: Detailed error messages with context


## Requirements

- PHP 8.2 or higher
- Composer

## Installation

```bash
composer require jbzoo/less
```

## Quick Start

```php
use JBZoo\Less\Less;

// Basic usage
$less = new Less();
$cssPath = $less->compile('/path/to/styles.less');

// With custom cache directory
$less = new Less(['cache_path' => './custom-cache']);
$cssPath = $less->compile('./assets/styles.less');
```

## Advanced Configuration

All configuration options are optional and can be customized based on your needs:

```php
use JBZoo\Less\Less;

try {
    $less = new Less([
        // Compilation behavior
        'force'        => false,                    // Force recompilation on each call
        'debug'        => false,                    // Enable source maps (future feature)

        // Path configuration
        'root_url'     => 'http://site.com/',       // Root URL for CSS asset references
        'root_path'    => '/full/path/to/site',     // Full path to web root directory

        // LESS features
        'global_vars'  => [                         // Global variables available in all files
            'primary-color' => '#007bff',           // Becomes @primary-color: #007bff;
            'font-size'     => '14px',              // Becomes @font-size: 14px;
        ],

        'autoload'     => [                         // Files automatically included before compilation
            '/full/path/to/mixins.less',
            '/full/path/to/variables.less',
        ],

        'import_paths' => [                         // Directory mappings for @import statements
            '/full/path/to/assets/less/' => 'http://site.com/assets/less/',
            './relative/path/to/less/'   => './relative/path/to/less/',
        ],

        // Caching configuration
        'cache_path'   => './cache',                // Cache directory location
        'cache_ttl'    => 2592000,                  // Cache TTL in seconds (30 days)

        // Custom functions (advanced feature)
        'functions' => [
            'str-reverse' => function ($arg) {
                $arg->value = strrev($arg->value);
                return $arg;
            },
        ],
    ]);

    // Add import paths dynamically
    $less->setImportPath(
        '/additional/import/directory/',
        'http://site.com/additional/directory/'     // URL mapping (optional)
    );

    // Compile LESS files
    $cssPath1 = $less->compile('/full/path/to/styles.less');
    $cssPath2 = $less->compile('./relative/path/to/styles.less');
    $cssPath3 = $less->compile(
        './relative/path/to/styles.less',
        'http://site.com/custom/base/path/'         // Override base path for URLs
    );

} catch (JBZoo\Less\Exception $e) {
    echo 'Compilation error: ' . $e->getMessage();
}
```

## Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `force` | bool | `false` | Force recompilation on every call, ignoring cache |
| `debug` | bool | `false` | Enable debug mode (future: source maps) |
| `root_url` | string | auto-detected | Base URL for asset references in CSS |
| `root_path` | string | auto-detected | Full filesystem path to web root |
| `global_vars` | array | `[]` | Global LESS variables available in all files |
| `autoload` | array | `[]` | LESS files to automatically include before compilation |
| `import_paths` | array | `[]` | Directory mappings for @import resolution |
| `cache_path` | string | `'./cache'` | Directory for storing compiled CSS files |
| `cache_ttl` | int | `2592000` | Cache time-to-live in seconds (30 days) |
| `functions` | array | `[]` | Custom PHP functions callable from LESS |


## Development

### Running Tests

```bash
# Install dependencies
make update

# Run all tests and code quality checks
make test-all

# Run only PHPUnit tests
make test

# Run only code style checks
make codestyle
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Run tests and ensure code quality (`make test-all`)
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

## License

MIT License. See [LICENSE](LICENSE) file for details.

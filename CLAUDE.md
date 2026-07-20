# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

JBZoo Less is a PHP wrapper library for LESS CSS compilation, specifically wrapping [wikimedia/less.php](https://github.com/wikimedia/less.php). It provides a simplified interface with caching, configuration management, and enhanced error handling for compiling LESS files to CSS.

## Development Commands

### Essential Commands
```bash
make update      # Install/update all dependencies via Composer
make test        # Run PHPUnit tests
make codestyle   # Run all linters and code quality checks
make test-all    # Run complete test suite (tests + codestyle)
```

### Testing Commands
```bash
vendor/bin/phpunit                                    # Run all tests
vendor/bin/phpunit tests/DriverGpeasyTest.php        # Run specific driver tests
vendor/bin/phpunit --filter testCompileSimple        # Run specific test method
```

The project inherits comprehensive make targets from `jbzoo/codestyle` including individual linting tools like `make test-phpstan`, `make test-psalm`, `make test-phpcsfixer`, etc.

## Architecture Overview

### Core Components

The library consists of 4 main classes in the `JBZoo\Less` namespace:

1. **Less** (`src/Less.php`) - Main public interface
   - Primary entry point for LESS compilation
   - Handles configuration validation and preparation
   - Coordinates between cache and driver systems
   - Public methods: `compile()`, `setImportPath()`

2. **Gpeasy** (`src/Gpeasy.php`) - Compiler driver
   - Wrapper around wikimedia/less.php compiler
   - Handles actual LESS compilation and import path management
   - Processes custom functions and variables

3. **Cache** (`src/Cache.php`) - File caching system
   - TTL-based file caching with automatic invalidation
   - Generates cache file names based on source file paths
   - Manages cache directory creation and cleanup

4. **Exception** (`src/Exception.php`) - Custom exception handling
   - Wraps underlying compiler exceptions with enhanced context

### Configuration System

The Less class accepts comprehensive configuration options covering:
- **Compilation behavior**: force recompilation, debug mode
- **Path management**: root URL/path, import paths, cache directory
- **LESS features**: global variables, autoloaded mixins, custom functions
- **Caching**: cache TTL and directory settings

### Testing Architecture

- **AbstractLessTest** - Base test class with comprehensive test suite
  - Tests compilation, caching, variables, imports, custom functions
  - File comparison utilities for validating CSS output
  - Environment setup (SERVER variables, cache cleanup)

- **DriverGpeasyTest** - Driver-specific test implementation
  - Inherits all tests from AbstractLessTest
  - Uses expected output files in `tests/expected-gpeasy/`

Test resources include LESS files, expected CSS outputs, and assets (images) for comprehensive testing scenarios.

## Key Dependencies

- **PHP 8.3+** requirement
- **wikimedia/less.php** (>=5.4.0) - Core LESS compiler
- **JBZoo ecosystem**:
  - `jbzoo/data` - Configuration data handling
  - `jbzoo/utils` - File system, URL, date utilities
  - `jbzoo/toolbox-dev` - Development tools and testing framework

## Usage Patterns

### Basic Compilation
```php
$less = new Less();
$cssPath = $less->compile('/path/to/styles.less');
```

### Advanced Configuration
```php
$less = new Less([
    'cache_path'   => './custom-cache',
    'global_vars'  => ['primary-color' => '#007bff'],
    'import_paths' => ['/assets/less/' => '/css/'],
    'functions'    => ['custom-func' => $callable],
]);
```

### Import Path Management
```php
$less->setImportPath('/full/path/to/imports/', 'http://site.com/imports/');
```
# JBZoo Less  [![Build Status](https://travis-ci.org/JBZoo/Less.svg?branch=master)](https://travis-ci.org/JBZoo/Less)      [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Less/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Less?branch=master)

PHP wrapper for any less-compilers. Now recommended to use [oyejorge/less.php](https://github.com/oyejorge/less.php)

[![License](https://poser.pugx.org/JBZoo/Less/license)](https://packagist.org/packages/JBZoo/Less)  [![Latest Stable Version](https://poser.pugx.org/JBZoo/Less/v/stable)](https://packagist.org/packages/JBZoo/Less) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/JBZoo/Less/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/JBZoo/Less/?branch=master)

## Install
```sh
composer require jbzoo/less:"1.x-dev"  # Last version
composer require jbzoo/less            # Stable version
```

## Usage
```php
<?php
require_once './vendor/autoload.php'; // composer autoload.php

// Get needed classes
use JBZoo\Less\Less;

try { // Any error handling

    // There is not option required
    $less = Less([
        'driver'       => 'gpeasy',             // Compiler's Driver
        'force'        => false,                // Can forced compile on each compile() calling
        'debug'        => false,                // On/Off Source map for browser debug console

        'root_url'     => 'http://site.com/',   // Root URL for compilled CSS files
                                                // For example - background:url('http://site.com/image.png')

        'root_path'    => '/full/path/to/site', // Full path to root of web directory

        'global_vars'  => [                     // Some vars that will be in all less files
            'main-color'  => '#f00',
            'media-print' => 'print',
        ],

        'autoload'     => [                     // Autoload before eash compiling
            '/full/path/to/my_mixins.less',     // See the best of coolection here
        ],                                      // https://github.com/JBZoo/JBlank/tree/master/less/misc

        'import_paths' => [                     // Import paths
            '/full/path/to/assets/less/' => 'http://site.com/assets/less/',
            './or/relative/path/to/dir/' => './or/relative/path/to/dir/',
        ],
        'cache_path'   => './cache',            // Where JBZoo/Less will save compiled CSS-files
        'cache_ttl'    => 2592000,              // How often rebuild css files (in seconds)
    ]);

    $less->setImportPath(
        '/full/path/to/other/import/directory/',    // Full or relative path
        'http://site.com/other/import/directory/'   // Not required
    );

    $fullCSSpath_1 = $less->compile('/full/path/to/styles.less');       // Basepath from config
    $fullCSSpath_2 = $less->compile('./relative/path/to/styles.less');  // OR relative path
    $fullCSSpath_3 = $less->compile(
        './relative/path/to/styles.less',
        'http://site.com/relative/path/to/'                             // Force base path for any URLs
    );

} catch (JBZoo\Less\Exception $e) {
    echo 'JBZoo/Less: ' . $e->getMessage();
}

```


## Unit tests and check code style
```sh
composer update-all
composer test
```


## License

MIT

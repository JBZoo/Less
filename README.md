# JBZoo / Less

[![Build Status](https://travis-ci.org/JBZoo/Less.svg)](https://travis-ci.org/JBZoo/Less)    [![Coverage Status](https://coveralls.io/repos/JBZoo/Less/badge.svg)](https://coveralls.io/github/JBZoo/Less)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Less/coverage.svg)](https://shepherd.dev/github/JBZoo/Less)    
[![Stable Version](https://poser.pugx.org/jbzoo/less/version)](https://packagist.org/packages/jbzoo/less)    [![Latest Unstable Version](https://poser.pugx.org/jbzoo/less/v/unstable)](https://packagist.org/packages/jbzoo/less)    [![Dependents](https://poser.pugx.org/jbzoo/less/dependents)](https://packagist.org/packages/jbzoo/less/dependents?order_by=downloads)    [![GitHub Issues](https://img.shields.io/github/issues/jbzoo/less)](https://github.com/JBZoo/Less/issues)    [![Total Downloads](https://poser.pugx.org/jbzoo/less/downloads)](https://packagist.org/packages/jbzoo/less/stats)    [![GitHub License](https://img.shields.io/github/license/jbzoo/less)](https://github.com/JBZoo/Less/blob/master/LICENSE)



PHP wrapper for [wikimedia/less.php](https://github.com/wikimedia/less.php). 


## Install
```sh
composer require jbzoo/less
```

## Usage
```php
use JBZoo\Less\Less;

try { // Any error handling

    // There is not option required
    $less = new Less([
        'force'        => false,                    // Can forced compile on each compile() calling
        'debug'        => false,                    // On/Off Source map for browser debug console

        'root_url'     => 'http://site.com/',       // Root URL for all CSS files and debug mode
                                                    // For example - background:url('http://site.com/image.png')

        'root_path'    => '/full/path/to/site',     // Full path to root of web directory

        'global_vars'  => [                         // Some vars that will be in all less files
            'color'  => '#f00',                     // @color: #f00;
            'media' => 'print',                     // @media: print;
        ],

        'autoload'     => [                         // Autoload before eash compiling
            '/full/path/to/my_mixins.less',         // See the best of collection here
        ],                                          // https://github.com/JBZoo/JBlank/tree/master/less/misc

        'import_paths' => [                         // Import paths
            '/full/path/to/assets/less/' => 'http://site.com/assets/less/',
            './or/relative/path/to/dir/' => './or/relative/path/to/dir/',
        ],

        'cache_path'   => './cache',                // Where JBZoo/Less will save compiled CSS-files
        'cache_ttl'    => 2592000,                  // How often rebuild css files (in seconds)

        'functions' => [                            // Custom functions for less (only for gpeasy!)
            'str-revert' => function ($arg) {       // Register name `str-revert()`
                $arg->value = strrev($arg->value);  // Just revert argument
                return $arg;                        // Result: str-revert('1234567890'); => '0987654321';
            },
        ],
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
make update
make test-all
```


## License

MIT

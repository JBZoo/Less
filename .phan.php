<?php

/**
 * JBZoo Toolbox - Less
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Less
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/Less
 */

declare(strict_types=1);

$default = include __DIR__ . '/vendor/jbzoo/codestyle/src/phan/default.php';

$phanConfig = array_merge($default, [
    'directory_list' => [
        'src',

        'vendor/jbzoo/data',
        'vendor/jbzoo/utils',
        'vendor/wikimedia/less.php/lib/Less',
    ],

    'file_list' => [
        'vendor/wikimedia/less.php/lib/Less/Parser.php',
    ]
]);

$phanConfig['plugins'] = array_filter($phanConfig['plugins'], function ($plugin) {
    return $plugin !== 'UnusedSuppressionPlugin';
});

return $phanConfig;

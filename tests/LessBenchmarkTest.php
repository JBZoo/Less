<?php
/**
 * JBZoo Less
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Less
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/Less
 */

namespace JBZoo\PHPUnit;

use JBZoo\Less\Less;
use JBZoo\Profiler\Benchmark;
use JBZoo\Utils\FS;

/**
 * Class LessBenchmarkTest
 * @package JBZoo\PHPUnit
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class LessBenchmarkTest extends PHPUnit
{
    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SERVER_PORT'] = '80';

        FS::rmDir(PROJECT_ROOT . '/cache');
        FS::rmDir(PROJECT_ROOT . '/tests/cache');
    }

    public function testCacheSpeed()
    {
        $less = new Less();
        $less->compile('tests/resources/simple.less'); // Cache file before tests

        Benchmark::compare([
            'Cache' => function () use ($less) {
                $less->compile('tests/resources/simple.less');
            }
        ], ['cache' => 1000]);

        isTrue(true);
    }
}

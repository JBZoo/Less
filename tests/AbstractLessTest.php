<?php

/**
 * JBZoo Toolbox - Less.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Less
 */

declare(strict_types=1);

namespace JBZoo\PHPUnit;

use JBZoo\Less\Less;
use JBZoo\Utils\FS;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 */
abstract class AbstractLessTest extends PHPUnit
{
    protected string $driver       = '';
    protected string $expectedPath = '';

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['SERVER_PORT'] = '80';

        FS::rmDir(PROJECT_ROOT . '/cache');
        FS::rmDir(PROJECT_ROOT . '/tests/cache');
    }

    public function testCompileSimple(): void
    {
        $less = new Less();

        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/simple.css';

        $this->isFileEq($expected, $actual);
    }

    public function testCompileInvalidLess(): void
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        $less = new Less();
        $less->compile('tests/resources/invalid.less');
    }

    public function testCompileUndefinedFile(): void
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        $less = new Less();
        $less->compile('tests/resources/undefined.less');
    }

    public function testCustomCachePath(): void
    {
        $uniqCacheFolder = \uniqid('', true);

        $less = new Less([
            'cache_path' => PROJECT_ROOT . '/tests/cache/' . $uniqCacheFolder,
        ]);

        $less->compile('tests/resources/simple.less');

        $actual   = PROJECT_ROOT . '/tests/cache/' . $uniqCacheFolder . '/tests_resources_simple_less.css';
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/simple.css';

        $this->isFileEq($expected, $actual);
    }

    public function testCustomRootUrl(): void
    {
        $less     = new Less(['root_url' => '//custom-site.com/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url.css';
        $this->isFileEq($expected, $actual);

        $less     = new Less(['root_url' => 'http://custom-site.com/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_http.css';
        $this->isFileEq($expected, $actual);

        $less     = new Less(['root_url' => 'https://custom-site.com/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_https.css';
        $this->isFileEq($expected, $actual);

        $less     = new Less(['root_url' => '.']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_dot.css';
        $this->isFileEq($expected, $actual);

        $less     = new Less(['root_url' => '../../path/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_complex.css';
        $this->isFileEq($expected, $actual);
    }

    public function testForceOff(): void
    {
        $less = new Less(['force' => 0]);
        $path = $less->compile('tests/resources/simple.less');
        \clearstatcache(false, $path);
        $mtimeExpected = \filemtime($path);

        \sleep(2);

        $path = $less->compile('tests/resources/simple.less');
        \clearstatcache(false, $path);
        $mtimeActual = \filemtime($path);

        isSame($mtimeExpected, $mtimeActual);
    }

    public function testForceOn(): void
    {
        $less = new Less(['force' => 1]);
        $path = $less->compile('tests/resources/simple.less');
        \clearstatcache(false, $path);
        $mtimeExpected = \filemtime($path);

        \sleep(1);

        $path = $less->compile('tests/resources/simple.less');
        \clearstatcache(false, $path);
        $mtimeActual = \filemtime($path);

        isNotSame($mtimeExpected, $mtimeActual);
    }

    public function testVars(): void
    {
        $less = new Less([
            'global_vars' => [
                'red'   => '#f00',
                'green' => '#0f0',
                'blue'  => '#00f',
            ],
        ]);

        $actual   = $less->compile('tests/resources/vars.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/vars.css';

        $this->isFileEq($expected, $actual);
    }

    public function testMixins(): void
    {
        $less = new Less([
            'autoload' => [
                PROJECT_ROOT . '/tests/resources/autoload-1.less',
                PROJECT_ROOT . '/tests/resources/autoload-2.less',
                PROJECT_ROOT . '/tests/resources/autoload-undefined.less',
            ],
        ]);

        $actual   = $less->compile('tests/resources/autoload.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/autoload.css';

        $this->isFileEq($expected, $actual);
    }

    public function testNestedImportPaths(): void
    {
        $less = new Less([
            'import_paths' => [
                PROJECT_ROOT . '/tests/resources/imported_2' => 'http://example.com/tests/resources/imported_2',
            ],
        ]);

        $actual   = $less->compile('tests/resources/import.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/import.css';

        $this->isFileEq($expected, $actual);
    }

    public function testImportPathsExternalMethod(): void
    {
        $less = new Less();

        $less->setImportPath(
            PROJECT_ROOT . '/tests/resources/imported_2',
            'http://example.com/tests/resources/imported_2',
        );

        $actual   = $less->compile('tests/resources/import.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/import.css';

        $this->isFileEq($expected, $actual);
    }

    public function testImportPathsUndefined(): void
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        $less = new Less();
        $less->setImportPath(PROJECT_ROOT . '/undefined/12356');
    }

    public function testDebugOn(): void
    {
        $less = new Less(['debug' => true]);

        $actual  = $less->compile('tests/resources/simple.less');
        $content = \file_get_contents($actual);
        isContain('sourceMappingURL=data:application/json', $content);
    }

    public function testDebugOff(): void
    {
        $less    = new Less();
        $actual  = $less->compile('tests/resources/simple.less');
        $content = \file_get_contents($actual);
        isNotContain('sourceMappingURL=data:application/json', $content);

        $this->setUp();
        $less    = new Less(['debug' => false]);
        $actual  = $less->compile('tests/resources/simple.less');
        $content = \file_get_contents($actual);
        isNotContain('sourceMappingURL=data:application/json', $content);
    }

    public function testSetCacheTTL(): void
    {
        $less = new Less(['cache_ttl' => 1]);

        $path = $less->compile('tests/resources/simple.less');
        \clearstatcache(false, $path);
        $mtimeExpected = \filemtime($path);

        \sleep(2);

        $path = $less->compile('tests/resources/simple.less');
        \clearstatcache(false, $path);
        $mtimeActual = \filemtime($path);

        isNotSame($mtimeExpected, $mtimeActual);
    }

    public function testCustomFunction(): void
    {
        $less = new Less([
            'functions' => [
                'str-revert' => static function ($arg) {
                    $arg->value = \strrev($arg->value);

                    return $arg;
                },
            ],
        ]);

        $actual   = $less->compile('tests/resources/function.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/function.css';

        $this->isFileEq($expected, $actual);
    }

    /**
     * Compare files.
     */
    protected function isFileEq(string $expectedFile, string $actualFile): void
    {
        $actual   = \file_get_contents($actualFile);
        $actual   = \trim(\preg_replace('#^(.*)' . \PHP_EOL . '#', '', $actual)); // remove first line (cache header)
        $expected = \trim(\file_get_contents($expectedFile));

        isSame($expected, $actual, 'File: ' . $expectedFile);
    }
}

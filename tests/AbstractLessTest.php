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
use JBZoo\Utils\FS;

/**
 * Class AbstractLessTest
 * @package JBZoo\PHPUnit
 * @SuppressWarnings(PHPMD.Superglobals)
 */
abstract class AbstractLessTest extends PHPUnit
{
    protected $driver       = '';
    protected $expectedPath = '';

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SERVER_PORT'] = '80';

        FS::rmDir(PROJECT_ROOT . '/cache');
        FS::rmDir(PROJECT_ROOT . '/tests/cache');
    }

    public function testUndefinedDriver()
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        (new Less(['driver' => 'undefined']));
    }

    public function testCompileSimple()
    {
        $less = new Less(['driver' => $this->driver]);

        $actual = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/simple.css';

        $this->isFileEq($expected, $actual);
    }

    public function testCompileInvalidLess()
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        $less = new Less(['driver' => $this->driver]);
        $less->compile('tests/resources/invalid.less');
    }

    public function testCompileUndefinedFile()
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        $less = new Less(['driver' => $this->driver]);
        $less->compile('tests/resources/undefined.less');
    }

    public function testCustomCachePath()
    {
        $uniqCacheFolder = uniqid('', true);

        $less = new Less([
            'driver'     => $this->driver,
            'cache_path' => PROJECT_ROOT . '/tests/cache/' . $uniqCacheFolder,
        ]);

        $less->compile('tests/resources/simple.less');

        $actual = PROJECT_ROOT . '/tests/cache/' . $uniqCacheFolder . '/tests_resources_simple_less.css';
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/simple.css';

        $this->isFileEq($expected, $actual);
    }

    public function testCustomRootUrl()
    {
        $less = new Less(['driver' => $this->driver, 'root_url' => '//custom-site.com/']);
        $actual = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url.css';
        $this->isFileEq($expected, $actual);

        $less = new Less(['driver' => $this->driver, 'root_url' => 'http://custom-site.com/']);
        $actual = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_http.css';
        $this->isFileEq($expected, $actual);

        $less = new Less(['driver' => $this->driver, 'root_url' => 'https://custom-site.com/']);
        $actual = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_https.css';
        $this->isFileEq($expected, $actual);

        $less = new Less(['driver' => $this->driver, 'root_url' => '.']);
        $actual = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_dot.css';
        $this->isFileEq($expected, $actual);

        $less = new Less(['driver' => $this->driver, 'root_url' => '../../path/']);
        $actual = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/custom_root_url_complex.css';
        $this->isFileEq($expected, $actual);
    }

    public function testForceOff()
    {
        $less = new Less(['driver' => $this->driver, 'force' => 0]);
        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeExpected = filemtime($path);

        sleep(2);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeActual = filemtime($path);

        isSame($mtimeExpected, $mtimeActual);
    }

    public function testForceOn()
    {
        $less = new Less(['driver' => $this->driver, 'force' => 1]);
        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeExpected = filemtime($path);

        sleep(1);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeActual = filemtime($path);

        isNotSame($mtimeExpected, $mtimeActual);
    }

    public function testVars()
    {
        $less = new Less([
            'driver'      => $this->driver,
            'global_vars' => [
                'red'   => '#f00',
                'green' => '#0f0',
                'blue'  => '#00f',
            ]
        ]);

        $actual = $less->compile('tests/resources/vars.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/vars.css';

        $this->isFileEq($expected, $actual);
    }

    public function testMixins()
    {
        $less = new Less([
            'driver'   => $this->driver,
            'autoload' => [
                PROJECT_ROOT . '/tests/resources/autoload-1.less',
                PROJECT_ROOT . '/tests/resources/autoload-2.less',
                PROJECT_ROOT . '/tests/resources/autoload-undefined.less',
            ]
        ]);

        $actual = $less->compile('tests/resources/autoload.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/autoload.css';

        $this->isFileEq($expected, $actual);
    }

    public function testNestedImportPaths()
    {
        $less = new Less([
            'driver'       => $this->driver,
            'import_paths' => [
                PROJECT_ROOT . '/tests/resources/imported_2' => 'http://example.com/tests/resources/imported_2',
            ],
        ]);

        $actual = $less->compile('tests/resources/import.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/import.css';

        $this->isFileEq($expected, $actual);
    }

    public function testImportPathsExternalMethod()
    {
        $less = new Less(['driver' => $this->driver]);

        $less->setImportPath(
            PROJECT_ROOT . '/tests/resources/imported_2',
            'http://example.com/tests/resources/imported_2'
        );

        $actual = $less->compile('tests/resources/import.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/import.css';

        $this->isFileEq($expected, $actual);
    }

    public function testImportPathsUndefined()
    {
        $this->expectException(\JBZoo\Less\Exception::class);

        $less = new Less(['driver' => $this->driver]);
        $less->setImportPath(PROJECT_ROOT . '/undefined/12356');
    }

    public function testDebugOn()
    {
        $less = new Less(['driver' => $this->driver, 'debug' => true]);

        $actual = $less->compile('tests/resources/simple.less');
        $content = file_get_contents($actual);
        isContain('sourceMappingURL=data:application/json', $content);
    }

    public function testDebugOff()
    {
        $less = new Less(['driver' => $this->driver]);
        $actual = $less->compile('tests/resources/simple.less');
        $content = file_get_contents($actual);
        isNotContain('sourceMappingURL=data:application/json', $content);

        $this->setUp();
        $less = new Less(['driver' => $this->driver, 'debug' => false]);
        $actual = $less->compile('tests/resources/simple.less');
        $content = file_get_contents($actual);
        isNotContain('sourceMappingURL=data:application/json', $content);
    }

    public function testSetCacheTTL()
    {
        $less = new Less(['driver' => $this->driver, 'cache_ttl' => 1]);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeExpected = filemtime($path);

        sleep(2);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeActual = filemtime($path);

        isNotSame($mtimeExpected, $mtimeActual);
    }

    public function testCustomFunction()
    {
        $less = new Less([
            'driver'    => $this->driver,
            'functions' => [
                'str-revert' => function ($arg) {
                    $arg->value = strrev($arg->value);
                    return $arg;
                },
            ],
        ]);

        $actual = $less->compile('tests/resources/function.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->expectedPath . '/function.css';

        $this->isFileEq($expected, $actual);
    }

    /**
     * Compare files
     * @param string $expectedFile
     * @param string $actualFile
     */
    protected function isFileEq($expectedFile, $actualFile)
    {
        $actual = file_get_contents($actualFile);
        $actual = trim(preg_replace('#^(.*)' . PHP_EOL . '#', '', $actual)); // remove first line (cache header)
        $expected = trim(file_get_contents($expectedFile));

        isSame($expected, $actual, 'File: ' . $expectedFile);
    }
}

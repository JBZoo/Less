<?php
/**
 * JBZoo Less
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   Less
 * @license   MIT
 * @copyright Copyright (C) JBZoo.com,  All rights reserved.
 * @link      https://github.com/JBZoo/Less
 * @author    Denis Smetannikov <denis@jbzoo.com>
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
    protected $_driver = '';
    protected $_expectedPath = '';

    protected function setUp()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['SERVER_PORT'] = '80';

        FS::rmdir(PROJECT_ROOT . '/cache');
        FS::rmdir(PROJECT_ROOT . '/tests/cache');
    }

    /**
     * @expectedException \JBZoo\Less\Exception
     */
    public function testUndefinedDriver()
    {
        (new Less(['driver' => 'undefined']));
    }

    public function testCompileSimple()
    {
        $less = new Less(['driver' => $this->_driver]);

        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/simple.css';

        $this->_isFileEq($expected, $actual);
    }

    /**
     * @expectedException \JBZoo\Less\Exception
     */
    public function testCompileInvalidLess()
    {
        $less = new Less(['driver' => $this->_driver]);
        $less->compile('tests/resources/invalid.less');
    }

    /**
     * @expectedException \JBZoo\Less\Exception
     */
    public function testCompileUndefinedFile()
    {
        $less = new Less(['driver' => $this->_driver]);
        $less->compile('tests/resources/undefined.less');
    }

    public function testCustomCachePath()
    {
        $uniqCacheFolder = uniqid();

        $less = new Less([
            'driver'     => $this->_driver,
            'cache_path' => PROJECT_ROOT . '/tests/cache/' . $uniqCacheFolder,
        ]);

        $less->compile('tests/resources/simple.less');

        $actual   = PROJECT_ROOT . '/tests/cache/' . $uniqCacheFolder . '/tests_resources_simple_less.css';
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/simple.css';

        $this->_isFileEq($expected, $actual);
    }

    public function testCustomRootUrl()
    {
        $less     = new Less(['driver' => $this->_driver, 'root_url' => '//custom-site.com/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/custom_root_url.css';
        $this->_isFileEq($expected, $actual);

        $less     = new Less(['driver' => $this->_driver, 'root_url' => 'http://custom-site.com/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/custom_root_url_http.css';
        $this->_isFileEq($expected, $actual);

        $less     = new Less(['driver' => $this->_driver, 'root_url' => 'https://custom-site.com/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/custom_root_url_https.css';
        $this->_isFileEq($expected, $actual);

        $less     = new Less(['driver' => $this->_driver, 'root_url' => '.']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/custom_root_url_dot.css';
        $this->_isFileEq($expected, $actual);

        $less     = new Less(['driver' => $this->_driver, 'root_url' => '../../path/']);
        $actual   = $less->compile('tests/resources/simple.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/custom_root_url_complex.css';
        $this->_isFileEq($expected, $actual);
    }

    public function testForceOff()
    {
        $less = new Less(['driver' => $this->_driver, 'force' => 0]);
        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeExpected = filemtime($path);

        sleep(1);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeActual = filemtime($path);

        isSame($mtimeExpected, $mtimeActual);
    }

    public function testForceOn()
    {
        $less = new Less(['driver' => $this->_driver, 'force' => 1]);
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
        $less = new Less(['driver' => $this->_driver, 'global_vars' => [
            'red'   => '#f00',
            'green' => '#0f0',
            'blue'  => '#00f',
        ]]);

        $actual   = $less->compile('tests/resources/vars.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/vars.css';

        $this->_isFileEq($expected, $actual);
    }

    public function testMixins()
    {
        $less = new Less(['driver' => $this->_driver, 'autoload' => [
            PROJECT_ROOT . '/tests/resources/autoload-1.less',
            PROJECT_ROOT . '/tests/resources/autoload-2.less',
            PROJECT_ROOT . '/tests/resources/autoload-undefined.less',
        ]]);

        $actual   = $less->compile('tests/resources/autoload.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/autoload.css';

        $this->_isFileEq($expected, $actual);
    }

    public function testNestedImportPaths()
    {
        $less = new Less([
            'driver'       => $this->_driver,
            'import_paths' => [
                PROJECT_ROOT . '/tests/resources/imported_2' => 'http://example.com/tests/resources/imported_2',
            ],
        ]);

        $actual   = $less->compile('tests/resources/import.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/import.css';

        $this->_isFileEq($expected, $actual);
    }

    public function testImportPathsExternalMethod()
    {
        $less = new Less(['driver' => $this->_driver]);

        $less->setImportPath(
            PROJECT_ROOT . '/tests/resources/imported_2',
            'http://example.com/tests/resources/imported_2'
        );

        $actual   = $less->compile('tests/resources/import.less');
        $expected = PROJECT_ROOT . '/tests/expected-' . $this->_expectedPath . '/import.css';

        $this->_isFileEq($expected, $actual);
    }

    /**
     * @expectedException \JBZoo\Less\Exception
     */
    public function testImportPathsUndefined()
    {
        $less = new Less(['driver' => $this->_driver]);
        $less->setImportPath(PROJECT_ROOT . '/undefined/12356');
    }

    public function testDebugOn()
    {
        $less = new Less(['driver' => $this->_driver, 'debug' => true]);

        $actual  = $less->compile('tests/resources/simple.less');
        $content = file_get_contents($actual);
        isContain('sourceMappingURL=data:application/json', $content);
    }

    public function testDebugOff()
    {
        $less    = new Less(['driver' => $this->_driver]);
        $actual  = $less->compile('tests/resources/simple.less');
        $content = file_get_contents($actual);
        isNotContain('sourceMappingURL=data:application/json', $content);

        $this->setUp();
        $less    = new Less(['driver' => $this->_driver, 'debug' => false]);
        $actual  = $less->compile('tests/resources/simple.less');
        $content = file_get_contents($actual);
        isNotContain('sourceMappingURL=data:application/json', $content);
    }

    public function testSetCacheTTL()
    {
        $less = new Less(['driver' => $this->_driver, 'cache_ttl' => 1]);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeExpected = filemtime($path);

        sleep(2);

        $path = $less->compile('tests/resources/simple.less');
        clearstatcache(false, $path);
        $mtimeActual = filemtime($path);

        isNotSame($mtimeExpected, $mtimeActual);
    }

    /**
     * Compare files
     * @param string $expectedFile
     * @param string $actualFile
     */
    protected function _isFileEq($expectedFile, $actualFile)
    {
        $actual   = file_get_contents($actualFile);
        $actual   = trim(preg_replace("#^(.*)" . PHP_EOL . "#", '', $actual)); // remove first line (cache header)
        $expected = trim(file_get_contents($expectedFile));

        isSame($expected, $actual, 'File: ' . $expectedFile);
    }
}

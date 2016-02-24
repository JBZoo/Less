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

/**
 * Class DriverILessTest
 * @package JBZoo\PHPUnit
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class DriverILessTest extends AbstractLessTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->_driver       = 'iless';
        $this->_expectedPath = 'iless';

        incomplete('"mishal/iless" too old package. Please use the "gpeasy" driver');
    }

    public function testMixins()
    {
        // ILess Driver don't support autoload option
        isFalse(false);
    }

    public function testDebugOn()
    {
        // ILess Driver don't support source map
        isFalse(false);
    }

    public function testNestedImportPaths()
    {
        // ILess Driver don't support nested imports
        isFalse(false);
    }

    public function testImportPathsExternalMethod()
    {
        // ILess Driver don't support nested imports managed by external method
        isFalse(false);
    }
}

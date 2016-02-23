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
 * Class DriverLeafoTest
 * @package JBZoo\PHPUnit
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class DriverLeafoTest extends AbstractLessTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->_driver = 'leafo';

        if (class_exists('\lessc_formatter_lessjs')) {
            $this->_expectedPath = 'leafo-real';
        } else {
            $this->_expectedPath = 'leafo-pseudo';
        }

        //incomplete('"leafo/lessphp" too old package. Please use the "gpeasy" driver');
    }

    public function testMixins()
    {
        // Leafo Driver don't support autoload option
        isFalse(false);
    }

    public function testDebugOn()
    {
        // Leafo Driver don't support source map
        isFalse(false);
    }
}

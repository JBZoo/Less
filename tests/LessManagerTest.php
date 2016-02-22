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

use JBZoo\Less\LessManager;
use JBZoo\Less\Exception;

/**
 * Class LessManagerTest
 * @package JBZoo\PHPUnit
 */
class LessManagerTest extends PHPUnit
{

    public function testShouldDoSomeStreetMagic()
    {
        $obj = new LessManager();

        is('street magic', $obj->doSomeStreetMagic());
    }

    /**
     * @expectedException \JBZoo\Less\Exception
     */
    public function testShouldShowException()
    {
        throw new Exception('Test message');
    }
}

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

/**
 * Class DriverGpeasyTest
 * @package JBZoo\PHPUnit
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class DriverGpeasyTest extends AbstractLessTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->driver = 'gpeasy';
        $this->expectedPath = 'gpeasy';
    }
}

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

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class DriverGpeasyTest extends AbstractLessTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->driver       = 'gpeasy';
        $this->expectedPath = 'gpeasy';
    }
}

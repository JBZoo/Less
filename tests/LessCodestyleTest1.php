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

namespace JBZoo\PHPUnit;

/**
 * Class LessCodestyleTest
 * @package JBZoo\PHPUnit
 */
class LessCopyrightTest extends AbstractCopyrightTest
{
    protected $packageName     = "Less";
    protected $isPhpStrictType = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->excludePaths[] = 'cache';
        $this->excludePaths[] = 'expected-gpeasy';
        $this->excludePaths[] = 'expected-iless';
        $this->excludePaths[] = 'expected-leafo-pseudo';
        $this->excludePaths[] = 'expected-leafo-real';
    }
}

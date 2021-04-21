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
 * Class LessReadmeTest
 *
 * @package JBZoo\PHPUnit
 */
class LessReadmeTest extends AbstractReadmeTest
{
    protected $packageName = 'Less';

    protected function setUp(): void
    {
        parent::setUp();
        $this->params['strict_types'] = true;
    }
}

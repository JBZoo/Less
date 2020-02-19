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

namespace JBZoo\Less\Driver;

use JBZoo\Data\Data;
use JBZoo\Less\Exception;
use JBZoo\Utils\FS;

/**
 * Class Driver
 * @package JBZoo\Less
 */
abstract class Driver
{
    /**
     * @var Data
     */
    protected $options;

    /**
     * @var mixed
     */
    protected $compiler;

    /**
     * @param Data $options
     */
    public function __construct(Data $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $fullPath
     * @param string $relPath
     * @return string
     */
    public function compile($fullPath, $relPath)
    {
        $this->initCompiler();
        $fullPath = FS::real($fullPath);

        return $this->compileFile($fullPath, $relPath);
    }

    /**
     * @return bool
     */
    protected function isDebug()
    {
        return $this->options->get('debug', false, 'bool');
    }

    /**
     * @param string      $fullPath
     * @param string|null $relPath
     * @throws Exception
     */
    abstract public function setImportPath($fullPath, $relPath = null): void;

    /**
     * @param string $fullPath
     * @param string $relPath
     * @return string
     */
    abstract protected function compileFile($fullPath, $relPath);

    /**
     * @return mixed
     */
    abstract protected function initCompiler();
}

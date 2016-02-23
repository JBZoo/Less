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

namespace JBZoo\Less\Driver;

use JBZoo\Data\Data;
use JBZoo\Less\Exception;

/**
 * Class Driver
 * @package JBZoo\Less
 */
abstract class Driver
{
    /**
     * @var Data
     */
    protected $_options;

    /**
     * @var mixed
     */
    protected $_compiler;

    /**
     * @param Data $options
     */
    public function __construct(Data $options)
    {
        $this->_options = $options;
    }

    /**
     * @param string $fullPath
     * @param string $relPath
     * @return string
     */
    public function compile($fullPath, $relPath)
    {
        $result = $this->_compile($fullPath, $relPath);

        return $result;
    }

    /**
     * @param string  $fullPath
     * @param  string $relPath
     * @return string
     * @throws Exception
     */
    abstract public function setImportPath($fullPath, $relPath);

    /**
     * @param string  $fullPath
     * @param  string $relPath
     * @return string
     */
    abstract protected function _compile($fullPath, $relPath);

    /**
     * @return mixed
     */
    abstract protected function _initCompiler();
}

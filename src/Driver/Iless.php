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

use ILess\Parser;
use JBZoo\Less\Exception;
use JBZoo\Utils\FS;

/**
 * Class Iless
 * @package JBZoo\Less
 * @codeCoverageIgnore
 */
class Iless extends Driver
{
    /**
     * @var Parser
     */
    protected $_compiler;

    /**
     * {@inheritdoc}
     */
    protected function _compile($fullPath, $relPath)
    {
        $this->_initCompiler();

        $this->_compiler->parseFile($fullPath);

        $resultCss = $this->_compiler->getCSS();

        return $resultCss;
    }

    /**
     * @return \Less_Parser
     */
    protected function _initCompiler()
    {
        if ($this->_compiler) {
            return $this->_compiler;
        }

        $this->_compiler = new Parser([
            'import_dirs' => $this->_options->get('import_paths'),
        ]);

        $this->_compiler->addVariables((array)$this->_options->get('global_vars', []));

        return $this->_compiler;
    }

    /**
     * {@inheritdoc}
     */
    public function setImportPath($fullPath, $relPath = null)
    {
        if (!FS::isDir($fullPath)) {
            throw new Exception('Undefined import path: ' . $fullPath);
        }
    }
}

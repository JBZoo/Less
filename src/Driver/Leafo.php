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

use JBZoo\Less\Exception;
use JBZoo\Utils\FS;

/**
 * Class Leafo
 * @package JBZoo\Less
 */
class Leafo extends Driver
{
    /**
     * @var \lessc
     */
    protected $_compiler;

    /**
     * {@inheritdoc}
     */
    protected function _compile($fullPath, $relPath)
    {
        return $this->_compiler->compileFile($fullPath);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initCompiler()
    {
        if ($this->_compiler) {
            return $this->_compiler;
        }

        $this->_compiler = new \lessc();

        if (class_exists('\lessc_formatter_lessjs')) {
            $formatter = new \lessc_formatter_lessjs();
            // configurate css view
            $formatter->openSingle        = ' { ';
            $formatter->closeSingle       = "}\n";
            $formatter->close             = "}\n";
            $formatter->indentChar        = '    ';
            $formatter->disableSingle     = true;
            $formatter->breakSelectors    = true;
            $formatter->assignSeparator   = ': ';
            $formatter->selectorSeparator = ', ';
            $this->_compiler->setFormatter($formatter);
        }

        $this->_compiler->setPreserveComments(false);

        // Set paths
        $importPaths = (array)$this->_options->get('import_paths', []);
        foreach ($importPaths as $fullPath => $relPath) {
            $this->setImportPath($fullPath, $relPath);
        }

        // Set paths
        $this->_compiler->setVariables((array)$this->_options->get('global_vars', []));

        return $this->_compiler;
    }

    /**
     * {@inheritdoc}
     */
    public function setImportPath($fullPath, $relPath = null)
    {
        $this->_initCompiler();

        if (!FS::isDir($fullPath)) {
            throw new Exception('Undefined import path: ' . $fullPath);
        }

        $fullPath = FS::getRelative($fullPath, $this->_options->get('root_path'));

        $this->_compiler->addImportDir($fullPath);
    }
}

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

use JBZoo\Less\Exception;
use JBZoo\Utils\FS;
use Less_Exception_Parser;
use Less_Parser;

/**
 * Class Gpeasy
 * @package JBZoo\Less
 */
class Gpeasy extends Driver
{
    /**
     * @var Less_Parser|null
     */
    protected $compiler;

    /**
     * {@inheritdoc}
     */
    protected function compileFile($fullPath, $relPath)
    {
        $this->initCompiler();

        $this->compiler->parseFile($fullPath, $relPath);

        return $this->compiler->getCss();
    }

    /**
     * @return Less_Parser
     * @throws Exception
     * @throws Less_Exception_Parser
     */
    protected function initCompiler()
    {
        if ($this->compiler) {
            return $this->compiler;
        }

        $options = [
            'compress'     => $this->options->get('compress', false),
            'strictUnits'  => false,
            'strictMath'   => false,
            'relativeUrls' => true,
            'cache_method' => false,
            'sourceMap'    => false,
            'indentation'  => '    ',
        ];

        if ($this->isDebug()) {
            $options['sourceMap'] = true;
            $options['sourceMapRootpath'] = $this->options->get('root_path');
            $options['sourceMapBasepath'] = $this->options->get('root_path');
        }

        // Create compiler
        $this->compiler = new Less_Parser($options);
        $this->compiler->Reset();

        // Global depends
        $mixins = $this->options->get('autoload');
        foreach ($mixins as $mixin) {
            $this->compiler->parseFile($mixin);
        }

        // Add custom vars
        $this->compiler->ModifyVars((array)$this->options->get('global_vars', []));

        // Set paths
        $importPaths = (array)$this->options->get('import_paths', []);
        foreach ($importPaths as $fullPath => $relPath) {
            $this->setImportPath($fullPath, $relPath);
        }

        // Set custom functions
        $functions = (array)$this->options->get('functions', [], 'arr');
        foreach ($functions as $name => $function) {
            $this->compiler->registerFunction($name, $function);
        }

        return $this->compiler;
    }

    /**
     * {@inheritdoc}
     */
    public function setImportPath($fullPath, $relPath = null): void
    {
        $this->initCompiler();

        $relPath = $relPath ?: $this->options->get('root_url');

        if (!FS::isDir($fullPath)) {
            throw new Exception('Undefined import path: ' . $fullPath);
        }

        $importPaths = Less_Parser::$options['import_dirs'];

        $importPaths[$fullPath] = $relPath;

        $this->compiler->SetImportDirs($importPaths);
    }
}

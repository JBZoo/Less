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

namespace JBZoo\Less;

use JBZoo\Data\Data;
use JBZoo\Utils\FS;
use Less_Exception_Parser;
use Less_Parser;

/**
 * Class Gpeasy
 * @package JBZoo\Less
 */
final class Gpeasy
{
    /**
     * @var Less_Parser|null
     * @phan-suppress PhanUndeclaredTypeProperty
     */
    protected ?Less_Parser $compiler = null;

    /**
     * @var Data
     */
    protected Data $options;

    /**
     * @param Data $options
     */
    public function __construct(Data $options)
    {
        $this->options = $options;
        $this->compiler = $this->initCompiler();

        // Set paths
        $importPaths = (array)$this->options->get('import_paths', []);
        foreach ($importPaths as $fullPath => $relPath) {
            $this->setImportPath((string)$fullPath, $relPath);
        }
    }


    /**
     * @param string $fullPath
     * @param string $relPath
     * @return string
     */
    public function compile(string $fullPath, string $relPath): string
    {
        $fullPath = FS::real($fullPath);
        if (!$fullPath) {
            throw new Exception("File '{$fullPath}' not found");
        }

        return $this->compileFile($fullPath, $relPath);
    }

    /**
     * @return bool
     */
    protected function isDebug(): bool
    {
        return (bool)$this->options->get('debug', false, 'bool');
    }

    /**
     * @param string $fullPath
     * @param string $relPath
     * @return string
     * @throws Less_Exception_Parser
     * @phan-suppress PhanUndeclaredClassMethod
     */
    protected function compileFile(string $fullPath, string $relPath): string
    {
        $this->initCompiler();

        if ($this->compiler) {
            $this->compiler->parseFile($fullPath, $relPath);

            return $this->compiler->getCss();
        }

        throw new Exception('Less processor is not initialized');
    }

    /**
     * @return Less_Parser
     * @throws Exception
     * @throws Less_Exception_Parser
     * @phan-suppress PhanUndeclaredTypeReturnType
     * @phan-suppress PhanUndeclaredClassMethod
     */
    protected function initCompiler(): Less_Parser
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
        $compiler = new Less_Parser($options);
        $compiler->Reset();

        // Global depends
        $mixins = $this->options->getArray('autoload');
        foreach ($mixins as $mixin) {
            $compiler->parseFile($mixin);
        }

        // Add custom vars
        $compiler->ModifyVars((array)$this->options->get('global_vars', []));

        // Set custom functions
        $functions = $this->options->getArray('functions', []);
        foreach ($functions as $name => $function) {
            $compiler->registerFunction((string)$name, $function);
        }

        return $compiler;
    }

    /**
     * @param string      $fullPath
     * @param string|null $relPath
     * @phan-suppress PhanUndeclaredClassStaticProperty
     * @phan-suppress PhanUndeclaredClassMethod
     */
    public function setImportPath(string $fullPath, ?string $relPath = null): void
    {
        $relPath = $relPath ?: $this->options->get('root_url');

        if (!FS::isDir($fullPath)) {
            throw new Exception('Undefined import path: ' . $fullPath);
        }

        $importPaths = Less_Parser::$options['import_dirs'];

        $importPaths[$fullPath] = $relPath;

        if ($this->compiler) {
            $this->compiler->SetImportDirs($importPaths);
        } else {
            throw new Exception('Less processor is not initialized');
        }
    }
}

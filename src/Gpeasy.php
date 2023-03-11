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

namespace JBZoo\Less;

use JBZoo\Data\Data;
use JBZoo\Utils\FS;

final class Gpeasy
{
    private \Less_Parser $compiler;

    private Data $options;

    public function __construct(Data $options)
    {
        $this->options  = $options;
        $this->compiler = $this->initCompiler();

        // Set paths
        $importPaths = $this->options->getArray('import_paths');

        foreach ($importPaths as $fullPath => $relPath) {
            $this->setImportPath((string)$fullPath, $relPath);
        }
    }

    public function compile(string $origfullPath, string $relPath): string
    {
        $fullPath = FS::real($origfullPath);
        if ($fullPath === '' || $fullPath === null) {
            throw new Exception("File '{$origfullPath}' not found");
        }

        return $this->compileFile($fullPath, $relPath);
    }

    public function setImportPath(string $fullPath, ?string $relPath = null): void
    {
        $relPath = $relPath === '' || $relPath === null
            ? $this->options->getString('root_url')
            : $relPath;

        if (!FS::isDir($fullPath)) {
            throw new Exception('Undefined import path: ' . $fullPath);
        }

        $importPaths = \Less_Parser::$options['import_dirs'];

        $importPaths[$fullPath] = $relPath;
        $this->compiler->SetImportDirs($importPaths);
    }

    private function isDebug(): bool
    {
        return $this->options->getBool('debug');
    }

    /**
     * @throws \Less_Exception_Parser
     */
    private function compileFile(string $fullPath, string $relPath): string
    {
        $this->initCompiler();
        $this->compiler->parseFile($fullPath, $relPath);

        return $this->compiler->getCss();
    }

    /**
     * @throws Exception
     * @throws \Less_Exception_Parser
     */
    private function initCompiler(): \Less_Parser
    {
        $options = [
            'compress'     => $this->options->getBool('compress'),
            'strictUnits'  => false,
            'strictMath'   => false,
            'relativeUrls' => true,
            'cache_method' => false,
            'sourceMap'    => false,
            'indentation'  => '    ',
        ];

        if ($this->isDebug()) {
            $options['sourceMap']         = true;
            $options['sourceMapRootpath'] = $this->options->getString('root_path');
            $options['sourceMapBasepath'] = $this->options->getString('root_path');
        }

        // Create compiler
        $compiler = new \Less_Parser($options);
        $compiler->Reset();

        // Global depends
        $mixins = $this->options->getArray('autoload');

        foreach ($mixins as $mixin) {
            $compiler->parseFile($mixin);
        }

        // Add custom vars
        $compiler->ModifyVars($this->options->getArray('global_vars'));

        // Set custom functions
        $functions = $this->options->getArray('functions', []);

        foreach ($functions as $name => $function) {
            $compiler->registerFunction((string)$name, $function);
        }

        return $compiler;
    }
}

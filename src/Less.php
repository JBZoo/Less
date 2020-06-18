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

namespace JBZoo\Less;

use JBZoo\Data\Data;
use JBZoo\Less\Driver\Gpeasy;
use JBZoo\Utils\FS;
use JBZoo\Utils\Sys;
use JBZoo\Utils\Url;
use RuntimeException;

/**
 * Class Less
 * @package JBZoo\Less
 */
class Less
{
    /**
     * @var array
     */
    protected $default = [
        'force'        => false,
        'debug'        => false,    // On/Off Source map for browser debug console
        'root_url'     => null,
        'root_path'    => null,
        'global_vars'  => [],
        'autoload'     => [],
        'import_paths' => [],
        'functions'    => [],
        'cache_path'   => './cache',
        'cache_ttl'    => 2592000,  // 30 days
    ];

    /**
     * @var Data
     */
    protected $options;

    /**
     * @var Gpeasy
     */
    protected $driver;

    /**
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->options = $this->prepareOptions($options);
        $this->driver = new Gpeasy($this->options);
    }

    /**
     * @param array $options
     * @return Data
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function prepareOptions(array $options)
    {
        // Default data for current system
        $this->default['root_url'] = Url::root();
        $this->default['root_path'] = Sys::getDocRoot();

        $options = array_merge($this->default, $options);

        // Check cache directory
        $cachePath = FS::clean((string)$options['cache_path']);
        if (!$cachePath) {
            throw new Exception('Option "cache_path" is empty!');
        }

        if (!FS::isDir($cachePath) && !mkdir($cachePath, 0755, true) && !is_dir($cachePath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cachePath));
        }

        $options['cache_path'] = FS::real($cachePath);

        $rootUrl = $options['root_url'] ?? '';
        $options['root_url'] = (string)rtrim((string)$rootUrl, '/');

        // Check mixin paths
        $lessFile = (array)$options['autoload'];
        foreach ($lessFile as $key => $mixin) {
            $lessFile[$key] = FS::real($mixin);
        }
        $options['autoload'] = array_filter($lessFile);

        // Check imported paths
        $importPaths = [];
        foreach ((array)$options['import_paths'] as $path => $uri) {
            if ($cleanPath = FS::real($path)) {
                $importPaths[$cleanPath] = $uri;
            }
        }
        $importPaths[(string)$options['root_path']] = $options['root_url']; // Forced add root path in the end of list!

        $options['import_paths'] = array_filter($importPaths);

        return new Data($options);
    }

    /**
     * @param string|null $basePath
     * @param string      $default
     * @return string
     */
    protected function prepareBasePath($basePath, $default)
    {
        $basePath = $basePath ?: $default;

        if (!Url::isAbsolute($basePath)) {
            $basePath = trim($basePath, '\\/');
            $basePath = $this->options->get('root_url') . '/' . $basePath;
        }

        return $basePath;
    }

    /**
     * @param string $lessFile
     * @param string $basePath
     * @return string
     * @throws Exception
     */
    public function compile($lessFile, $basePath = null)
    {
        try {
            $basePath = $this->prepareBasePath($basePath, dirname($lessFile));

            $cache = new Cache($this->options);
            $cache->setFile($lessFile, $basePath);

            $isForce = $this->options->get('force', false, 'bool');

            if ($isForce || $cache->isExpired()) {
                $result = $this->driver->compile($lessFile, $basePath);
                $cache->save($result);
            }

            $cssPath = $cache->getFile();
        } catch (\Exception $exception) { // Rewrite exception type
            $message = 'JBZoo/Less: ' . $exception->getMessage();
            $trace = $exception->getTraceAsString();

            throw new Exception($message . PHP_EOL . $trace);
        }

        return $cssPath;
    }

    /**
     * @param string      $fullPath
     * @param string|null $relPath
     * @throws Exception
     */
    public function setImportPath($fullPath, $relPath = null): void
    {
        $relPath = $relPath ?: $this->options->get('root_url');
        $this->driver->setImportPath($fullPath, $relPath);
    }
}

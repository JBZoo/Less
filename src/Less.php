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

namespace JBZoo\Less;

use JBZoo\Less\Driver\Driver;
use JBZoo\Data\Data;
use JBZoo\Utils\FS;
use JBZoo\Utils\Sys;
use JBZoo\Utils\Url;

/**
 * Class Less
 * @package JBZoo\Less
 */
class Less
{
    /**
     * @var array
     */
    protected $_default = [
        'driver'       => 'gpeasy', // Recomended
        'force'        => false,
        'debug'        => false,    // On/Off Source map for browser debug console
        'root_url'     => null,
        'root_path'    => null,
        'global_vars'  => [],
        'autoload'     => [],
        'import_paths' => [],
        'functions'    => [],
        'cache_path'   => './cache',
        'cache_ttl'    => 2592000,
    ];

    /**
     * @var Data
     */
    protected $_options;

    /**
     * @var Driver
     */
    protected $_driver;

    /**
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = array())
    {
        $this->_options = $this->_prepareOptions($options);
        $driverName     = $this->_options->get('driver');

        $driverClass = __NAMESPACE__ . '\\Driver\\' . $driverName;
        if (!class_exists($driverClass)) {
            throw new Exception('Undefined driver: ' . $driverName);
        }

        $this->_driver = new $driverClass($this->_options);
    }

    /**
     * @param array $options
     * @return Data
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _prepareOptions(array $options)
    {
        // Default data for current system
        $this->_default['root_url']  = Url::root();
        $this->_default['root_path'] = Sys::getDocRoot();

        $options = array_merge($this->_default, $options);

        // Check cache directory
        $cachePath = FS::clean($options['cache_path']);
        if (!FS::isDir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $options['cache_path'] = FS::real($cachePath);
        $options['root_url']   = rtrim($options['root_url'], '/');
        $options['root_path']  = FS::real($options['root_path']);
        $options['driver']     = ucfirst(strtolower(trim($options['driver'])));

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
        $importPaths[$options['root_path']] = $options['root_url']; // Forced add root path in the end of list!

        $options['import_paths'] = array_filter($importPaths);

        return new Data($options);
    }

    /**
     * @param string|null $basepath
     * @param string      $default
     * @return string
     */
    protected function _prepareBasepath($basepath, $default)
    {
        $basepath = $basepath ?: $default;

        if (!Url::isAbsolute($basepath)) {
            $basepath = trim($basepath, '\\/');
            $basepath = $this->_options->get('root_url') . '/' . $basepath;
        }

        return $basepath;
    }

    /**
     * @param string $lessfile
     * @param string $basepath
     * @return string
     * @throws Exception
     */
    public function compile($lessfile, $basepath = null)
    {
        try {
            $basepath = $this->_prepareBasepath($basepath, dirname($lessfile));

            $cache = new Cache($this->_options);
            $cache->setFile($lessfile, $basepath);

            $isForce = $this->_options->get('force', false, 'bool');

            if ($isForce || $cache->isExpired()) {
                $result = $this->_driver->compile($lessfile, $basepath);
                $cache->save($result);
            }

            $csspath = $cache->getFile();

        } catch (\Exception $e) { // Rewrite exception type

            $message = 'JBZoo/Less: ' . $e->getMessage();
            $trace   = $e->getTraceAsString();

            throw new Exception($message . PHP_EOL . $trace);
        }

        return $csspath;
    }

    /**
     * @param string $fullPath
     * @param null   $relPath
     */
    public function setImportPath($fullPath, $relPath = null)
    {
        $relPath = $relPath ?: $this->_options->get('root_url');
        $this->_driver->setImportPath($fullPath, $relPath);
    }
}

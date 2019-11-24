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

namespace JBZoo\Less;

use JBZoo\Data\Data;
use JBZoo\Utils\Filter;
use JBZoo\Utils\FS;
use JBZoo\Utils\Slug;
use JBZoo\Utils\Str;
use JBZoo\Utils\Vars;

/**
 * Class Cache
 * @package JBZoo\Less
 */
class Cache
{
    /**
     * @var int
     */
    protected $cache_ttl = 2592000; // 30 days

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $base;

    /**
     * @var string
     */
    protected $resultFile;

    /**
     * @var string
     */
    protected $less;

    /**
     * @var Data
     */
    protected $options;

    /**
     * @param Data $options
     */
    public function __construct(Data $options)
    {
        $this->options = $options;
        $this->setCacheTTL($this->options->get('cache_ttl'));
    }

    /**
     * @param string $lessFile
     * @param string $basePath
     */
    public function setFile($lessFile, $basePath)
    {
        $this->less = FS::real($lessFile);
        $this->base = FS::clean($basePath);

        $this->hash = $this->_getHash();
        $this->resultFile = $this->_getResultFile();
    }

    /**
     * Check is current cache is expired
     */
    public function isExpired()
    {
        if (!FS::isFile($this->resultFile)) {
            return true;
        }

        $fileAge = abs(time() - filemtime($this->resultFile));
        if ($fileAge >= $this->cache_ttl) {
            return true;
        }

        $firstLine = trim(FS::firstLine($this->resultFile));
        $expected = trim($this->_getHeader());

        return $expected !== $firstLine;
    }

    /**
     * @return string
     */
    protected function _getResultFile()
    {
        $relPath = FS::getRelative($this->less, $this->options->get('root_path'));

        // Normalize relative path
        $relPath = Slug::filter($relPath, '_');
        $relPath = Str::low($relPath);

        // Get full clean path
        $fullPath = FS::real($this->options->get('cache_path')) . '/' . $relPath . '.css';
        $fullPath = FS::clean($fullPath);

        return $fullPath;
    }

    /**
     * @return string
     */
    protected function _getHash()
    {
        // Check depends
        $mixins = $this->options->get('autoload', [], 'arr');
        $hashes = [];
        foreach ($mixins as $mixin) {
            $hashes[$mixin] = md5_file($mixin);
        }
        ksort($hashes);

        $options = $this->options->getArrayCopy();
        $options['functions'] = array_keys($options['functions']);
        ksort($options);

        $hashed = [
            'less'     => $this->less,
            'less_md5' => md5_file($this->less),
            'base'     => $this->base,
            'mixins'   => $hashes,
            'options'  => $options,
        ];

        $hashed = serialize($hashed);

        return md5($hashed); // md5 is faster than sha1!
    }

    /**
     * @return string
     */
    protected function _getHeader()
    {
        return '/* cache-id:' . $this->hash . ' */' . PHP_EOL;
    }

    /**
     * Save result to cache
     *
     * @param string $content
     * @throws Exception
     */
    public function save($content)
    {
        $content = $this->_getHeader() . $content;
        $result = file_put_contents($this->resultFile, $content);

        if (!$result) {
            throw new Exception('JBZoo/Less: File not save - ' . $this->resultFile); // @codeCoverageIgnore
        }
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->resultFile;
    }

    /**
     * @param int $newTTL In seconds (1 to 365 days)
     */
    public function setCacheTTL($newTTL)
    {
        $newTTL = Filter::int($newTTL);
        $newTTL = Vars::limit($newTTL, 1, 86400 * 365);

        $this->cache_ttl = $newTTL;
    }
}

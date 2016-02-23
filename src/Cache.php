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
    protected $_cache_ttl = 2592000; // 30 days

    /**
     * @var string
     */
    protected $_hash;

    /**
     * @var string
     */
    protected $_base;

    /**
     * @var string
     */
    protected $_resultFile;

    /**
     * @var string
     */
    protected $_less;

    /**
     * @var Data
     */
    protected $_options;

    /**
     * @param Data $options
     */
    public function __construct(Data $options)
    {
        $this->_options = $options;
        $this->setCacheTTL($this->_options->get('cache_ttl'));
    }

    /**
     * @param string $lessfile
     * @param string $basepath
     */
    public function setFile($lessfile, $basepath)
    {
        $this->_less = FS::real($lessfile);
        $this->_base = FS::clean($basepath);

        $this->_hash       = $this->_getHash();
        $this->_resultFile = $this->_getResultFile();
    }

    /**
     * Check is current cache is expired
     */
    public function isExpired()
    {
        if (!FS::isFile($this->_resultFile)) {
            return true;
        }

        $fileAge = abs(time() - filemtime($this->_resultFile));
        if ($fileAge >= $this->_cache_ttl) {
            return true;
        }

        $firstLine = trim(FS::firstLine($this->_resultFile));
        $expected  = trim($this->_getHeader());
        if ($expected === $firstLine) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function _getResultFile()
    {
        $relPath = FS::getRelative($this->_less, $this->_options->get('root_path'));

        // Normalize relative path
        $relPath = Slug::filter($relPath, '_');
        $relPath = Str::low($relPath);

        // Gett full clean path
        $fullPath = FS::real($this->_options->get('cache_path')) . '/' . $relPath . '.css';
        $fullPath = FS::clean($fullPath);

        return $fullPath;
    }

    /**
     * @return string
     */
    protected function _getHash()
    {
        // Check depends
        $mixins = $this->_options->get('autoload', [], 'arr');
        $hashes = [];
        foreach ($mixins as $mixin) {
            $hashes[$mixin] = md5_file($mixin);
        }
        ksort($hashes);

        $options = $this->_options->getArrayCopy();
        ksort($options);

        $hashed = [
            'less'     => $this->_less,
            'less_md5' => md5_file($this->_less),
            'base'     => $this->_base,
            'mixins'   => $hashes,
            'options'  => $options,
        ];

        $hashed = serialize($hashed);
        $hash   = md5($hashed); // md5 is faster than sha1!

        return $hash;
    }

    /**
     * @return string
     */
    protected function _getHeader()
    {
        return '/* cacheid:' . $this->_hash . ' */' . PHP_EOL;
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
        $result  = file_put_contents($this->_resultFile, $content);

        if (!$result) {
            throw new Exception('JBZoo/Less: File not save - ' . $this->_resultFile); // @codeCoverageIgnore
        }
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->_resultFile;
    }

    /**
     * @param int $newTTL In seconds (1 to 365 days)
     */
    public function setCacheTTL($newTTL)
    {
        $newTTL = Filter::int($newTTL);
        $newTTL = Vars::limit($newTTL, 1, 86400 * 365);

        $this->_cache_ttl = $newTTL;
    }
}

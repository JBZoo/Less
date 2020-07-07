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
use JBZoo\Utils\Dates;
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
    protected $cacheTtl = Dates::MONTH;

    /**
     * @var string
     */
    protected $hash = '';

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var string
     */
    protected $resultFile = '';

    /**
     * @var string
     */
    protected $lessFilepath = '';

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
    public function setFile(string $lessFile, string $basePath): void
    {
        $lessFilepath = FS::real($lessFile);
        if (!$lessFilepath) {
            throw new Exception("File '{$lessFile}' not found");
        }

        $this->lessFilepath = $lessFilepath;
        $this->basePath = FS::clean($basePath);

        $this->hash = $this->getHash();
        $this->resultFile = $this->getResultFile();
    }

    /**
     * Check is current cache is expired
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!FS::isFile($this->resultFile)) {
            return true;
        }

        $fileAge = (int)(time() - filemtime($this->resultFile));
        $fileAge = abs($fileAge);

        if ($fileAge >= $this->cacheTtl) {
            return true;
        }

        $firstLine = trim((string)FS::firstLine($this->resultFile));
        $expected = trim($this->getHeader());

        return $expected !== $firstLine;
    }

    /**
     * @return string
     */
    protected function getResultFile(): string
    {
        $relPath = FS::getRelative($this->lessFilepath, $this->options->get('root_path'));

        // Normalize relative path
        $relPath = Slug::filter($relPath, '_');
        $relPath = Str::low($relPath);

        // Get full clean path
        if ($cacheBasePath = FS::real($this->options->get('cache_path'))) {
            $fullPath = "{$cacheBasePath}/{$relPath}.css";
            $fullPath = FS::clean($fullPath);
        } else {
            throw new Exception('Cache directory is not found');
        }

        return $fullPath;
    }

    /**
     * @return string
     */
    protected function getHash(): string
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
            'less'     => $this->lessFilepath,
            'less_md5' => md5_file($this->lessFilepath),
            'base'     => $this->basePath,
            'mixins'   => $hashes,
            'options'  => $options,
        ];

        $hashed = serialize($hashed);

        return md5($hashed); // md5 is faster than sha1!
    }

    /**
     * @return string
     */
    protected function getHeader(): string
    {
        return "/* cache-id:{$this->hash} */\n";
    }

    /**
     * Save result to cache
     *
     * @param string $content
     * @throws Exception
     */
    public function save(string $content): void
    {
        $content = $this->getHeader() . $content;
        $result = file_put_contents($this->resultFile, $content);

        if (!$result) {
            throw new Exception('JBZoo/Less: File not save - ' . $this->resultFile);
        }
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->resultFile;
    }

    /**
     * @param int $newTTL In seconds (1 to 365 days)
     */
    public function setCacheTTL(int $newTTL): void
    {
        $newTTL = Filter::int($newTTL);
        $newTTL = Vars::limit($newTTL, 1, Dates::YEAR);

        $this->cacheTtl = $newTTL;
    }
}

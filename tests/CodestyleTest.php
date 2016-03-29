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

namespace JBZoo\PHPUnit;

use Symfony\Component\Finder\Finder;

/**
 * Class CodestyleTest
 * @package JBZoo\PHPUnit
 */
class CodestyleTest extends Codestyle
{
    protected $_packageName = "Less";
    protected $_packageAuthor = 'Denis Smetannikov <denis@jbzoo.com>';

    /**
     * Ignore list for
     * @var array
     */
    protected $_excludePaths = array(
        '.git',
        '.idea',
        'bin',
        'bower_components',
        'build',
        'fonts',
        'logs',
        'node_modules',
        'resources',
        'vendor',

        // Only for JBZoo/Less
        'expected-gpeasy',
        'expected-iless',
        'expected-leafo-pseudo',
        'expected-leafo-real',
    );

    /**
     * {@inheritdoc}
     */
    public function testHeadersCSS()
    {
        $valid = $this->_prepareTemplate(implode($this->_validHeaderCSS, $this->_le));

        $finder = new Finder();
        $finder
            ->files()
            ->in(PROJECT_ROOT)
            ->exclude($this->_excludePaths)
            ->name('*.css')
            ->notName('*_less.css') // Only for JBZoo/Less
            ->notName('*.min.css');

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $content = openFile($file->getPathname());
            isContain($valid, $content, false, 'File has no valid header: ' . $file);
        }
    }

    /**
     * Test line endings
     */
    public function testFiles()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(PROJECT_ROOT)
            ->notName('*_less.css') // Only for JBZoo/Less
            ->exclude($this->_excludePaths);

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $content = openFile($file->getPathname());
            isNotContain("\r", $content, false, 'File has \r symbol: ' . $file);
            isNotContain("\t", $content, false, 'File has \t symbol: ' . $file);
        }
    }
}

<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Theme\Wrapper;

/**
 * The class can be used to read the wrapper group data from the theme.xml file
 *
 * It looks for an element "wrapper-groups". This element can contain "css-group" and "js-group"
 * elements. The "name" attribute is needed, a "recursive" attribute is optional and allows
 * to use subdirectories in "css-group" elements.
 *
 * Each "*-group" element can contain several "file" elements with "href" attributes.
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Group {

  /**
   * Absolute local path and filename of the theme.xml.
   *
   * @var string
   */
  private $_themeFile = '';

  /**
   * Buffer property for the document object with the loaded theme.xml.
   *
   * @var \DOMDocument
   */
  private $_document = NULL;

  /**
   * Initialize object and remember $themeFile for lazy loading.
   *
   * @param string $themeFile
   */
  public function __construct($themeFile) {
    $this->_themeFile = $themeFile;
  }

  /**
   * Fetch files for this wrapper group.
   *
   * @param string $name group name
   * @param string $mode js or css
   * @return array(string)
   */
  public function getFiles($name, $mode = 'css') {
    $files = array();
    $document = $this->getDocument();
    $xpath = new \DOMXpath($document);
    $query = sprintf(
      '//wrapper-groups/%s-group[@name = "%s"]/file',
      \Papaya\Utility\Text\XML::escapeAttribute($mode),
      \Papaya\Utility\Text\XML::escapeAttribute($name)
    );
    foreach ($xpath->evaluate($query) as $file) {
      $fileName = $xpath->evaluate('string(@href)', $file);
      if (!empty($fileName)) {
        $files[] = $fileName;
      }
    }
    return $files;
  }

  /**
   * Check if subdirectories are allows for the wrapper group
   *
   * @param string $name group name
   * @param string $mode js or css
   * @return boolean
   */
  public function allowDirectories($name, $mode = 'css') {
    $document = $this->getDocument();
    $xpath = new \DOMXpath($document);
    $query = sprintf(
      'boolean(//wrapper-groups/%s-group[@name = "%s"]/@recursive = "yes")',
      \Papaya\Utility\Text\XML::escapeAttribute($mode),
      \Papaya\Utility\Text\XML::escapeAttribute($name)
    );
    return $xpath->evaluate($query);
  }

  /**
   * Get the document, create the document and loads the theme file if nessesary.
   *
   * @return \DOMDocument|NULL
   */
  public function getDocument() {
    if (is_null($this->_document)) {
      $document = new \DOMDocument('1.0', 'UTF-8');
      if ($document->load($this->_themeFile)) {
        $this->_document = $document;
      }
    }
    return $this->_document;
  }

  /**
   * Set the document object (Dependency Injection)
   *
   * @param \DOMDocument $document
   */
  public function setDocument(\DOMDocument $document) {
    $this->_document = $document;
  }
}

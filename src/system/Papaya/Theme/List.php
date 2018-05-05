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

/**
* Load a list of themes. The themes are subdirectories in a local directory.
*
* @package Papaya-Library
* @subpackage Theme
*/
class PapayaThemeList extends \PapayaObject implements IteratorAggregate {

  private $_handler = NULL;

  /**
   * Return an iterator for the theme list.
   *
   * @see \IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return new \PapayaIteratorCallback(
      new \PapayaIteratorGlob($this->handler()->getLocalPath().'*', GLOB_ONLYDIR),
      array($this, 'callbackGetName')
    );
  }

  /**
   * strip path information from returned directory name
   *
   * @param string $element
   * @return string
   */
  public function callbackGetName($element) {
    return basename($element);
  }

  /**
   * Load the dynamic value defintion from the theme.xml and return it
   *
   * @param string $theme
   * @return \PapayaContentStructure
   */
  public function getDefinition($theme) {
    return $this->handler()->getDefinition($theme);
  }

  /**
   * The handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param \PapayaThemeHandler $handler
   * @return \PapayaThemeHandler
   */
  public function handler(\PapayaThemeHandler $handler = NULL) {
    if (isset($handler)) {
      $this->_handler = $handler;
    } elseif (NULL === $this->_handler) {
      $this->_handler = new \PapayaThemeHandler();
      $this->_handler->papaya($this->papaya());
    }
    return $this->_handler;
  }
}

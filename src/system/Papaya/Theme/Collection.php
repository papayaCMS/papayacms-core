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

namespace Papaya\Theme;
/**
 * Load a list of themes. The themes are subdirectories in a local directory.
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Collection extends \Papaya\Application\BaseObject implements \IteratorAggregate {

  private $_handler = NULL;

  /**
   * Return an iterator for the theme list.
   *
   * @see \IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return new \Papaya\Iterator\Callback(
      new \Papaya\Iterator\Glob($this->handler()->getLocalPath().'*', GLOB_ONLYDIR),
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
   * @return \Papaya\Content\Structure
   */
  public function getDefinition($theme) {
    return $this->handler()->getDefinition($theme);
  }

  /**
   * The handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param \Papaya\Theme\Handler $handler
   * @return \Papaya\Theme\Handler
   */
  public function handler(\Papaya\Theme\Handler $handler = NULL) {
    if (isset($handler)) {
      $this->_handler = $handler;
    } elseif (NULL === $this->_handler) {
      $this->_handler = new \Papaya\Theme\Handler();
      $this->_handler->papaya($this->papaya());
    }
    return $this->_handler;
  }
}

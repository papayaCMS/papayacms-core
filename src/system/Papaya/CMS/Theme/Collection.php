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
namespace Papaya\CMS\Theme;

use Papaya\Application;
use Papaya\Iterator;

/**
 * Load a list of themes. The themes are subdirectories in a local directory.
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Collection implements Application\Access, \IteratorAggregate {
  use Application\Access\Aggregation;

  /**
   * @var
   */
  private $_handler;

  /**
   * Return an iterator for the theme list.
   *
   * @see \IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return new Iterator\Callback(
      new Iterator\Glob($this->handler()->getLocalPath().'*', GLOB_ONLYDIR),
      function($element) {
        return \basename($element);
      }
    );
  }

  /**
   * Load the dynamic value defintion from the theme.xml and return it
   *
   * @param string $theme
   *
   * @return \Papaya\CMS\Content\Structure
   */
  public function getDefinition($theme) {
    return $this->handler()->getDefinition($theme);
  }

  /**
   * The handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param Handler $handler
   *
   * @return Handler
   */
  public function handler(Handler $handler = NULL) {
    if (NULL !== $handler) {
      $this->_handler = $handler;
    } elseif (NULL === $this->_handler) {
      $this->_handler = new Handler();
      $this->_handler->papaya($this->papaya());
    }
    return $this->_handler;
  }
}

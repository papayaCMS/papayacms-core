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

namespace Papaya\Administration\Theme\Editor;

use \Papaya\Content;
use \Papaya\Theme;
use \Papaya\UI;
use \Papaya\XML;

/**
 * Main part of the theme skins editor (dynamic values for a theme)
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Changes extends \Papaya\Administration\Page\Part {

  private $_commands;
  /**
   * @var Content\Theme\Skin
   */
  private $_themeSet;

  /**
   * @var Theme\Handler
   */
  private $_themeHandler;

  /**
   * Append changes commands to parent xml element
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $parent->append($this->commands());
  }

  /**
   * Commands, actual actions
   *
   * @param UI\Control\Command\Controller|UI\Control\Command $commands
   * @return UI\Control\Command\Controller|UI\Control\Command
   */
  public function commands(UI\Control\Command $commands = NULL) {
    if (NULL !== $commands) {
      $this->_commands = $commands;
    } elseif (NULL === $this->_commands) {
      $this->_commands = new UI\Control\Command\Controller('cmd');
      $this->_commands->owner($this);
      $this->_commands['skin_edit'] =
      $command = new Changes\Skin\Change($this->themeSet());
      $this->_commands['skin_delete'] =
      $command = new Changes\Skin\Remove($this->themeSet());
      $this->_commands['skin_import'] =
      $command = new Changes\Skin\Import(
        $this->themeSet(), $this->themeHandler()
      );
      $this->_commands['skin_export'] =
      $command = new Changes\Skin\Export(
        $this->themeSet(), $this->themeHandler()
      );
      $this->_commands['values_edit'] =
      $command = new Changes\Dialog($this->themeSet());
    }
    return $this->_commands;
  }

  /**
   * The theme skin the the database record wrapper object.
   *
   * @param Content\Theme\Skin $themeSet
   * @return Content\Theme\Skin
   */
  public function themeSet(Content\Theme\Skin $themeSet = NULL) {
    if (NULL !== $themeSet) {
      $this->_themeSet = $themeSet;
    } elseif (NULL === $this->_themeSet) {
      $this->_themeSet = new Content\Theme\Skin();
    }
    return $this->_themeSet;
  }

  /**
   * The theme handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param Theme\Handler $themeHandler
   * @return Theme\Handler
   */
  public function themeHandler(Theme\Handler $themeHandler = NULL) {
    if (NULL !== $themeHandler) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new Theme\Handler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }
}

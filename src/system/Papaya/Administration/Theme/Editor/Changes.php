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

/**
 * Main part of the theme sets editor (dynamic values for a theme)
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Changes extends \Papaya\Administration\Page\Part {

  private $_commands = NULL;
  /**
   * @var \Papaya\Content\Theme\Set
   */
  private $_themeSet = NULL;

  /**
   * @var \PapayaThemeHandler
   */
  private $_themeHandler = NULL;

  /**
   * Append changes commands to parent xml element
   *
   * @param \PapayaXmlElement $parent
   */
  public function appendTo(\PapayaXmlElement $parent) {
    $parent->append($this->commands());
  }

  /**
   * Commands, actual actions
   *
   * @param \PapayaUiControlCommandController|\PapayaUiControlCommand $commands
   * @return \PapayaUiControlCommandController|\PapayaUiControlCommand
   */
  public function commands(\PapayaUiControlCommand $commands = NULL) {
    if (isset($commands)) {
      $this->_commands = $commands;
    } elseif (is_null($this->_commands)) {
      $this->_commands = new \PapayaUiControlCommandController('cmd');
      $this->_commands->owner($this);
      $this->_commands['set_edit'] =
      $command = new Changes\Set\Change($this->themeSet());
      $this->_commands['set_delete'] =
      $command = new Changes\Set\Remove($this->themeSet());
      $this->_commands['set_import'] =
      $command = new Changes\Set\Import(
        $this->themeSet(), $this->themeHandler()
      );
      $this->_commands['set_export'] =
      $command = new Changes\Set\Export(
        $this->themeSet(), $this->themeHandler()
      );
      $this->_commands['values_edit'] =
      $command = new Changes\Dialog($this->themeSet());
    }
    return $this->_commands;
  }

  /**
   * The theme set the the database record wrapper object.
   *
   * @param \Papaya\Content\Theme\Set $themeSet
   * @return \Papaya\Content\Theme\Set
   */
  public function themeSet(\Papaya\Content\Theme\Set $themeSet = NULL) {
    if (isset($themeSet)) {
      $this->_themeSet = $themeSet;
    } elseif (NULL === $this->_themeSet) {
      $this->_themeSet = new \Papaya\Content\Theme\Set();
    }
    return $this->_themeSet;
  }

  /**
   * The theme handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param \PapayaThemeHandler $themeHandler
   * @return \PapayaThemeHandler
   */
  public function themeHandler(\PapayaThemeHandler $themeHandler = NULL) {
    if (isset($themeHandler)) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new \PapayaThemeHandler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }
}

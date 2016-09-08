<?php
/**
* Administration page parts are interactive ui controls, with access to a toolbar.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Administration
* @version $Id: Part.php 38950 2013-11-20 13:52:17Z weinert $
*/

/**
* Administration page parts are interactive ui controls, with access to a toolbar.
*
* @package Papaya-Library
* @subpackage Administration
*/
abstract class PapayaAdministrationPagePart extends PapayaUiControlInteractive {

  /**
   * @var PapayaUiControlCommand
   */
  private $_commands = NULL;

  /**
   * @var PapayaUiToolbarSet
   */
  private $_toolbar = NULL;

  /**
   * @var PapayaAdministrationPage
   */
  private $_page = NULL;

  public function __construct(PapayaAdministrationPage $page = NULL) {
    $this->_page = $page;
  }

  /**
   * @return PapayaAdministrationPage
   */
  public function getPage() {
    return $this->_page;
  }

  /**
   * Execute command controller and append output. Page parts are append in the order of
   * (Content -> Navigation -> Information). They share their parameters.
   *
   * @param PapayaXMlElement $parent
   */
  public function appendTo(PapayaXMlElement $parent) {
    $parent->append($this->commands());
  }

  /**
   * Getter/Setter for the commands subobject,
   * {@see PapayaAdministrationPagePart::_createCommands89} is called for lazy init
   *
   * @param PapayaUiControlCommand $commands
   * @return PapayaUiControlCommand
   */
  public function commands(PapayaUiControlCommand $commands = NULL) {
    if (isset($commands)) {
      $this->_commands = $commands;
    } elseif (NULL === $this->_commands) {
      $this->_commands = $this->_createCommands();
    }
    return $this->_commands;
  }

  /**
   * Overload this method to create the commands structure.
   *
   * @param string $name
   * @param string $default
   * @return PapayaUiControlCommandController
   */
  protected function _createCommands($name = 'cmd', $default = 'show') {
    $commands = new PapayaUiControlCommandController($name, $default);
    $commands->owner($this);
    return $commands;
  }

  /**
   * Toolbar Set each page parts has it's own toolbar set, the sets are merged by the page
   * after all page parts are appened. The order of the sets is different from the page parts
   * (Navigation -> Content -> Information).
   *
   * @param PapayaUiToolbarSet $toolbar
   * @return PapayaUiToolbarSet
   */
  public function toolbar(PapayaUiToolbarSet $toolbar = NULL) {
    if (isset($toolbar)) {
      $this->_toolbar = $toolbar;
      if (count($toolbar->elements) < 1) {
        $this->_initializeToolbar($this->_toolbar);
      }
    } elseif (is_null($this->_toolbar)) {
      $this->_toolbar = $toolbar = new PapayaUiToolbarSet();
      $toolbar->papaya($this->papaya());
      $this->_initializeToolbar($toolbar);
    }
    return $this->_toolbar;
  }

  /**
   * Initialize the toolbar with buttons and other elements
   *
   * @param PapayaUiToolbarSet $toolbar
   */
  protected function _initializeToolbar(PapayaUiToolbarSet $toolbar) {
  }
}

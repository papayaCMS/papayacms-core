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

namespace Papaya\Administration\Page;

/**
 * Administration page parts are interactive ui controls, with access to a toolbar.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
abstract class Part extends \Papaya\UI\Control\Interactive {

  /**
   * @var \Papaya\UI\Control\Command
   */
  private $_commands = NULL;

  /**
   * @var \Papaya\UI\Toolbar\Collection
   */
  private $_toolbar = NULL;

  /**
   * @var \Papaya\Administration\Page
   */
  private $_page = NULL;

  public function __construct(\Papaya\Administration\Page $page = NULL) {
    $this->_page = $page;
  }

  /**
   * @return \Papaya\Administration\Page
   */
  public function getPage() {
    return $this->_page;
  }

  /**
   * Execute command controller and append output. Page parts are append in the order of
   * (Content -> Navigation -> Information). They share their parameters.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $parent->append($this->commands());
  }

  /**
   * Getter/Setter for the commands subobject,
   * {@see \Papaya\Administration\Page\Part::_createCommands89} is called for lazy init
   *
   * @param \Papaya\UI\Control\Command $commands
   * @return \Papaya\UI\Control\Command
   */
  public function commands(\Papaya\UI\Control\Command $commands = NULL) {
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
   * @return \Papaya\UI\Control\Command\Controller
   */
  protected function _createCommands($name = 'cmd', $default = 'show') {
    $commands = new \Papaya\UI\Control\Command\Controller($name, $default);
    $commands->owner($this);
    return $commands;
  }

  /**
   * Toolbar Set each page parts has it's own toolbar set, the sets are merged by the page
   * after all page parts are appened. The order of the sets is different from the page parts
   * (Navigation -> Content -> Information).
   *
   * @param \Papaya\UI\Toolbar\Collection $toolbar
   * @return \Papaya\UI\Toolbar\Collection
   */
  public function toolbar(\Papaya\UI\Toolbar\Collection $toolbar = NULL) {
    if (isset($toolbar)) {
      $this->_toolbar = $toolbar;
      if (!$toolbar->elements || count($toolbar->elements) < 1) {
        $this->_initializeToolbar($this->_toolbar);
      }
    } elseif (is_null($this->_toolbar)) {
      $this->_toolbar = $toolbar = new \Papaya\UI\Toolbar\Collection();
      $toolbar->papaya($this->papaya());
      $this->_initializeToolbar($toolbar);
    }
    return $this->_toolbar;
  }

  /**
   * Initialize the toolbar with buttons and other elements
   *
   * @param \Papaya\UI\Toolbar\Collection $toolbar
   */
  protected function _initializeToolbar(\Papaya\UI\Toolbar\Collection $toolbar) {
  }
}

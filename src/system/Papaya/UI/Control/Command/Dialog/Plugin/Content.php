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

namespace Papaya\UI\Control\Command\Dialog\Plugin;
/**
 * A command that executes a dialog. After dialog creation, and after successfull/failed execution
 * callbacks are executed. This class adds read and write the data to an plugin content object
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Content extends \Papaya\UI\Control\Command\Dialog {

  private $_content;

  /**
   * This dialog command uses database record objects
   *
   * @param \Papaya\Plugin\Editable\Content $content
   */
  public function __construct(\Papaya\Plugin\Editable\Content $content) {
    $this->_content = $content;
  }

  /**
   * Getter/Setter for the database record
   *
   * @return \Papaya\Plugin\Editable\Content
   */
  public function getContent() {
    return $this->_content;
  }

  /**
   * Execute command and append result to output xml
   *
   * @param \Papaya\Xml\Element
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $showDialog = TRUE;
    $dialog = $this->dialog();
    if ($dialog->execute()) {
      $this->getContent()->assign($dialog->data());
      $this->callbacks()->onExecuteSuccessful($dialog, $parent);
      $showDialog = !$this->hideAfterSuccess();
    } elseif ($dialog->isSubmitted()) {
      $this->callbacks()->onExecuteFailed($dialog, $parent);
    }
    if ($showDialog) {
      return $dialog->appendTo($parent);
    }
    return $parent;
  }

  /**
   * Create the dialog object and assign the content data to it.
   *
   * @see \Papaya\UI\Control\Command\Dialog::createDialog()
   * @return \Papaya\UI\Dialog
   */
  protected function createDialog() {
    $dialog = parent::createDialog();
    $dialog->data()->assign($this->getContent());
    return $dialog;
  }
}

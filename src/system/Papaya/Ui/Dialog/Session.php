<?php
/**
* A dialog that stores its data into the session.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Session.php 36671 2012-01-24 17:18:19Z weinert $
*/

/**
* A dialog that stores its data into the session.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogSession extends PapayaUiDialog {

  private $_sessionIdentifier = NULL;

  /**
  * The session identifer does not need to be a string,
  * read {@see PapayaSessionValues::_compileIdentifer()} for more information.
  *
  * @param mixed $sessionIdentifier
  */
  public function __construct($sessionIdentifier = NULL) {
    $this->_sessionIdentifier = empty($sessionIdentifier) ? $this : $sessionIdentifier;
  }

  /**
  * Execute the dialog, load and save the session value.
  *
  * @return boolean
  */
  public function execute() {
    $data = $this->papaya()->session->getValue($this->_sessionIdentifier);
    if (is_array($data) && !empty($data)) {
      $this->data()->merge($data);
    }
    if (parent::execute()) {
      $this->papaya()->session->setValue($this->_sessionIdentifier, $this->data()->toArray());
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Reset the session variable.
  */
  public function reset() {
    $this->papaya()->session->setValue($this->_sessionIdentifier, NULL);
  }
}
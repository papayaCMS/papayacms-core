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

namespace Papaya\UI\Dialog;

/**
 * A dialog that stores its data into the session.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Session extends \Papaya\UI\Dialog {
  private $_sessionIdentifier;

  /**
   * The session identifier does not need to be a string,
   * read {@see \Papaya\Session\Values::_compileIdentifer()} for more information.
   *
   * @param mixed $sessionIdentifier
   * @param object|null $owner
   */
  public function __construct($sessionIdentifier = NULL, $owner = NULL) {
    parent::__construct($owner);
    $this->_sessionIdentifier = empty($sessionIdentifier) ? $this : $sessionIdentifier;
  }

  /**
   * Execute the dialog, load and save the session value.
   *
   * @return bool
   */
  public function execute() {
    $data = $this->papaya()->session->getValue($this->_sessionIdentifier);
    if (\is_array($data) && !empty($data)) {
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

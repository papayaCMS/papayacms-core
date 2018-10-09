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

use Papaya\Database\Interfaces as DatabaseInterfaces;
use Papaya\UI;

/**
 * A dialog superclass for dialogs that execute database actions on
 * {@see \Papaya\Database\BaseObject\Record} instances
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Database extends UI\Dialog {
  /**
   * Attached database callbacks object
   *
   * @var \Papaya\Database\BaseObject\Record
   */
  private $_callbacks;

  /**
   * Attached database record object
   *
   * @var DatabaseInterfaces\Record
   */
  private $_record;

  /**
   * Create dialog and attach a record to it.
   *
   * @param DatabaseInterfaces\Record $record
   * @param null|object $owner
   */
  public function __construct(DatabaseInterfaces\Record $record, $owner = NULL) {
    parent::__construct($owner);
    $this->record($record);
  }

  /**
   * Getter/Setter for the database record object. The record is set in the constructor always.
   * But it can be changed or accessed using this method.
   *
   * The values of the record are merged into the data property.
   *
   * @param DatabaseInterfaces\Record $record
   *
   * @return DatabaseInterfaces\Record
   */
  public function record(DatabaseInterfaces\Record $record = NULL) {
    if (NULL !== $record) {
      $this->_record = $record;
      $this->data()->merge((array)$record->toArray());
    }
    return $this->_record;
  }

  /**
   * Getter/Setter for the callbacks, if you set your own callback object, make sure it has the
   * needed definitions.
   *
   * @param Database\Callbacks $callbacks
   *
   * @return Database\Callbacks
   */
  public function callbacks(Database\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Database\Callbacks();
    }
    return $this->_callbacks;
  }
}

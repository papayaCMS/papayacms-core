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
namespace Papaya\UI\Control\Command\Condition;

use Papaya\Database;
use Papaya\UI;

/**
 * A command condition based on a database records existence.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Record extends UI\Control\Command\Condition {
  /**
   * member variable to store the record
   *
   * @var Database\Interfaces\Record
   */
  private $_record;

  /**
   * Create object and store callback.
   *
   * @param Database\Interfaces\Record $record
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(Database\Interfaces\Record $record) {
    $this->_record = $record;
  }

  /**
   * Execute callback and return value. Returns true if the record exists
   *
   * @return bool
   */
  public function validate() {
    return $this->_record->key()->exists();
  }
}

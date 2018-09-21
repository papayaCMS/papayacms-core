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
namespace Papaya\Content\Phrase;

use Papaya\Content;
use Papaya\Database;

/**
 * Log messages for phrases system
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Messages extends Database\Records {
  /**
   * Map field names to more convenient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'id' => 'log_id',
    'phrase' => 'log_phrase',
    'phrase_id' => 'log_phrase_id',
    'text' => 'log_msg',
    'group' => 'log_module',
    'created' => 'log_datetime'
  ];

  protected $_tableName = Content\Tables::PHRASE_LOG;

  public function add(array $data) {
    $values = $this->mapping()->mapPropertiesToFields($data);
    $this->getDatabaseAccess()->insertRecord(
      $this->getDatabaseAccess()->getTableName($this->_tableName),
      $this->mapping()->getField('id'),
      $values
    );
  }
}

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
namespace Papaya\Database;

/**
 * Papaya Database Result, this will be a new result interface for database queries
 *
 * For now it provides constants to specifiy the fetch mode.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
interface Result extends \IteratorAggregate {
  /**
   * Fetch numeric and named keys
   *
   * @var int
   */
  const FETCH_BOTH = 0;

  /**
   * Fetch numeric keys
   *
   * @var int
   */
  const FETCH_ORDERED = 1;

  /**
   * Fetch named keys
   *
   * @var int
   */
  const FETCH_ASSOC = 2;

  /**
   * Fetch row from result
   *
   * @param int $mode
   *
   * @return array
   */
  public function fetchRow($mode = self::FETCH_ORDERED);

  /**
   * Fetch row from result into associative array
   *
   * @return array
   */
  public function fetchAssoc();

  /**
   * Fetch field from result
   *
   * @param int|string $column
   *
   * @return mixed
   */
  public function fetchField($column = 0);

  /**
   * Seek internal pointer to the given row
   *
   * @param int $index
   *
   * @return array
   */
  public function seek($index);

  /**
   * return count of records in compiled result with limit
   *
   * @return int
   */
  public function count();

  /**
   * return count of records in compiled result without limit
   *
   * @return int
   */
  public function absCount();

  /**
   * Unset result data
   */
  public function free();

  /**
   * @return null|\Papaya\Message\Context\Data
   */
  public function getExplain();
}

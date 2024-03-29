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
namespace Papaya\CMS\Administration\Pages\Dependency;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * Loads and returns the current counting of dependencies and references for an page id
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Counter extends Database\BaseObject {
  /**
   * store page id
   *
   * @var int
   */
  private $_pageId;

  /**
   * store loading status - for lazy loading
   *
   * @var bool
   */
  private $_loaded = FALSE;

  /**
   * Store actual amount loaded from database
   *
   * @var array('dependencies' => integer,'references' => integer)
   */
  protected $_amounts = [
    'dependencies' => 0,
    'references' => 0
  ];

  /**
   * Create object, validate page id argument and store it
   *
   * @param int $pageId
   */
  public function __construct(int $pageId) {
    $this->_pageId = $pageId;
  }

  public function getPageID(): int {
    return $this->_pageId;
  }

  /**
   * Return dependencies count for current page. Triggers lazy loading.
   *
   * @return int
   */
  public function getDependencies() {
    $this->lazyLoad();
    return $this->_amounts['dependencies'];
  }

  /**
   * Return references count for current page. Triggers lazy loading.
   *
   * @return int
   */
  public function getReferences() {
    $this->lazyLoad();
    return $this->_amounts['references'];
  }

  /**
   * Load countings for dependencies and references from database
   *
   * @return int
   */
  public function load() {
    $this->_amounts = [
      'dependencies' => 0,
      'references' => 0
    ];
    $sql = 'SELECT \'dependencies\' AS name, COUNT(*) counter
              FROM %1$s
             WHERE topic_origin_id = %3$d
            UNION ALL
            SELECT \'references\' AS name, COUNT(*) counter
              FROM %2$s
             WHERE topic_source_id = %3$d
                OR topic_target_id = %3$d';
    $parameters = [
      $this->databaseGetTableName(Content\Tables::PAGE_DEPENDENCIES),
      $this->databaseGetTableName(Content\Tables::PAGE_REFERENCES),
      $this->_pageId
    ];
    if ($databaseResult = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $databaseResult->fetchRow(Database\Result::FETCH_ASSOC)) {
        $this->_amounts[$row['name']] = $row['counter'];
      }
      return $this->_loaded = TRUE;
    }
    return FALSE;
  }

  /**
   * Trigger loading if it was not already done.
   */
  private function lazyLoad() {
    if (!$this->_loaded) {
      $this->_loaded = $this->load();
    }
  }

  /**
   * Get a label showing the countings. The label can be empty, contain only the references or
   * dependencies and references. If it contains the dependencies the references are shown even if
   * they are zero.
   *
   * @param string $separator
   * @param string $prefix
   * @param string $suffix
   *
   * @return string
   */
  public function getLabel($separator = '/', $prefix = ' (', $suffix = ')') {
    $this->lazyLoad();
    $result = '';
    if (\array_sum($this->_amounts) > 0) {
      $result .= $prefix;
      if ($this->_amounts['dependencies'] > 0) {
        $result .= $this->_amounts['dependencies'];
        $result .= $separator;
      }
      $result .= $this->_amounts['references'];
      $result .= $suffix;
    }
    return $result;
  }
}

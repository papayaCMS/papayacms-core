<?php
/**
* Loads and returns the current counting of dependencies and references for an page id
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Counter.php 39730 2014-04-07 21:05:30Z weinert $
*/

/**
* Loads and returns the current counting of dependencies and references for an page id
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencyCounter extends PapayaDatabaseObject {

  /**
  * store page id
  *
  * @var integer
  */
  private $_pageId = 0;

  /**
  * store loading status - for lazy loading
  *
  * @var boolean
  */
  private $_loaded = FALSE;

  /**
  * Store actual counting loaded from database
  *
  * @var array('dependencies' => integer,'references' => integer)
  */
  protected $_countings = array(
    'dependencies' => 0,
    'references' => 0
  );

  /**
  * Create object, validate page id argument and store it
  *
  * @param integer $pageId
  */
  public function __construct($pageId) {
    PapayaUtilConstraints::assertInteger($pageId);
    $this->_pageId = $pageId;
  }

  /**
  * Return dependencies count for current page. Triggers lazy loading.
  *
  * @return integer
  */
  public function getDependencies() {
    $this->lazyLoad();
    return $this->_countings['dependencies'];
  }

  /**
  * Return references count for current page. Triggers lazy loading.
  *
  * @return integer
  */
  public function getReferences() {
    $this->lazyLoad();
    return $this->_countings['references'];
  }

  /**
   * Load countings for dependencies and references from database
   *
   * @return integer
   */
  public function load() {
    $this->_countings = array(
      'dependencies' => 0,
      'references' => 0
    );
    $sql = "SELECT 'dependencies' AS name, COUNT(*) counter
              FROM %1\$s
             WHERE topic_origin_id = %3\$d
            UNION ALL
            SELECT 'references' AS name, COUNT(*) counter
              FROM %2\$s
             WHERE topic_source_id = %3\$d
                OR topic_target_id = %3\$d";
    $parameters = array(
      $this->databaseGetTableName(PapayaContentTables::PAGE_DEPENDENCIES),
      $this->databaseGetTableName(PapayaContentTables::PAGE_REFERENCES),
      $this->_pageId
    );
    if ($databaseResult = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $databaseResult->fetchRow(PapayaDatabaseResult::FETCH_ASSOC)) {
        $this->_countings[$row['name']] = $row['counter'];
      }
      return TRUE;
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
  * @return string
  */
  public function getLabel($separator = '/', $prefix = ' (', $suffix = ')') {
    $this->lazyLoad();
    $result = '';
    if (array_sum($this->_countings) > 0) {
      $result .= $prefix;
      if ($this->_countings['dependencies'] > 0) {
        $result .= $this->_countings['dependencies'];
        $result .= $separator;
      }
      $result .= $this->_countings['references'];
      $result .= $suffix;
    }
    return $result;
  }
}

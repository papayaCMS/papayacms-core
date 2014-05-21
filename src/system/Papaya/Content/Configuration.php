<?php
/**
* Provide data encapsulation for the configuration options.
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
* @subpackage Content
* @version $Id: Configuration.php 36051 2011-08-05 16:32:54Z weinert $
*/

/**
* Provide data encapsulation for the configuration options.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentConfiguration
  extends PapayaDatabaseObjectList {

  /**
  * Map field names to value identfiers
  *
  * @var array
  */
  protected $_fieldMapping = array(
    'opt_name' => 'name',
    'opt_value' => 'value'
  );

  /**
  * Load all options
  */
  public function load() {
    $sql = "SELECT opt_name, opt_value
              FROM %s
             ORDER BY opt_name";
    $parameters = array(
      $this->databaseGetTableName(PapayaContentTables::OPTIONS)
    );
    return $this->_loadRecords($sql, $parameters, 'opt_name');
  }
}
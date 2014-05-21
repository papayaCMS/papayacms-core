<?php
/**
* Use the environtment variable (from $_SERVER) as cache data
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
* @subpackage Plugins
* @version $Id: Environment.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* Request parameters are used to create cache condition data.
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaCacheIdentifierDefinitionEnvironment
  implements PapayaCacheIdentifierDefinition {

  private $_name = 'QUERY_STRING';

  /**
   * Store the name of the environment variable
   *
   * @param string $name
   */
  public function __construct($name) {
    PapayaUtilConstraints::assertString($name);
    PapayaUtilConstraints::assertNotEmpty($name);
    $this->_name = $name;
  }

  /**
   * If the environment variable is empty, it is not relevant for the cache identifier so return
   * TRUE. In all other cases return the name of the variable and the value
   *
   * @return TRUE|array
   */
  public function getStatus() {
    return empty($_SERVER[$this->_name])
      ? TRUE
      : array(get_class($this) => array($this->_name => $_SERVER[$this->_name]));
  }

  /**
   * Any kind of data from the request environment
   *
   * @see PapayaCacheIdentifierDefinition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_REQUEST;
  }
}

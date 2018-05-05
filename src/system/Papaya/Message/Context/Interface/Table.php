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

/**
* Interface for message string contexts
*
* Message context can be converted to a unformatted string
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessageContextInterfaceTable
  extends \PapayaMessageContextInterfaceList {

  /**
  * Get table column header if available
  *
  * @return array|NULL
  */
  function getColumns();

  /**
  * Get the data row count
  * @return integer
  */
  function getRowCount();

  /**
   * Get data row by position
   * @param $position
   * @return array
   */
  function getRow($position);
}

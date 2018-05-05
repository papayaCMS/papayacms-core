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
* This object loads the defined output modes for a papaya installation.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentViewModes extends \PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'extension' => 'viewmode_ext',
    'type' => 'viewmode_type'
  );

  /**
  * Table containing domain informations
  *
  * @var string
  */
  protected $_tableName = \PapayaContentTables::VIEW_MODES;

  protected $_identifierProperties = array('extension');
}

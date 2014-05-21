<?php
/**
* Load status informations about a page.
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
* @version $Id: Status.php 36360 2011-10-28 13:11:07Z weinert $
*/

/**
* Load status informations about a page.
*
* @package Papaya-Library
* @subpackage Content
*
* @property integer $id
* @property integer $sessionMode
*/
class PapayaContentPageStatus extends PapayaDatabaseRecord {

  protected $_fields = array(
    'id' => 'topic_id',
    'sessionMode' => 'topic_sessionmode'
  );

  protected $_tableName = PapayaContentTables::PAGES;

}
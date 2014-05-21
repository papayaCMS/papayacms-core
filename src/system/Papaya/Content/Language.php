<?php
/**
* Provide data encapsulation for a language record.
*
* Allows to load/save the page translation.
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
* @subpackage Content
* @version $Id: Language.php 38308 2013-03-21 13:17:30Z weinert $
*/

/**
* Provide data encapsulation for a language record.
*
* Allows to load/save the page translation.
*
* @package Papaya-Library
* @subpackage Content
*
* @property integer $id
* @property string $identifier
* @property string $code
* @property string $title
* @property string $image
* @property integer $isInterface
* @property integer $isContent
*/
class PapayaContentLanguage extends PapayaDatabaseRecordLazy {

  /**
  * Map properties to database fields
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'id' => 'lng_id',
    'identifier' => 'lng_ident',
    'code' => 'lng_short',
    'title' => 'lng_title',
    'image' => 'lng_glyph',
    'is_interface' => 'is_interface_lng',
    'is_content' => 'is_content_lng'
  );

  protected $_tableName = PapayaContentTables::LANGUAGES;
}

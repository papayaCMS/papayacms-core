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
namespace Papaya\CMS\Content;

use Papaya\Database;
use Papaya\Request\ContentLanguage;

/**
 * Provide data encapsulation for a language record.
 *
 * Allows to load/save the page translation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $id
 * @property string $identifier
 * @property string $code
 * @property string $title
 * @property string $image
 * @property int $isInterface
 * @property int $isContent
 */
class Language extends Database\Record\Lazy implements ContentLanguage {
  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'id' => 'lng_id',
    'identifier' => 'lng_ident',
    'code' => 'lng_short',
    'title' => 'lng_title',
    'image' => 'lng_glyph',
    'is_interface' => 'is_interface_lng',
    'is_content' => 'is_content_lng'
  ];

  protected $_tableName = Tables::LANGUAGES;

  /**
   * For BC allow to read the properties using the field names, this allows to drop in
   * the language object for the old language record array
   *
   * @param string $name
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($name) {
    switch ($name) {
      case 'lng_id' :
        return parent::offsetGet('id');
      case 'lng_ident' :
        return parent::offsetGet('identifier');
      case 'lng_short' :
        return parent::offsetGet('code');
      case 'lng_title' :
        return parent::offsetGet('title');
    }
    return parent::offsetGet($name);
  }

  /**
   * @param string $name
   * @return bool
   */
  public function offsetExists($name): bool {
    switch ($name) {
      case 'lng_id' :
      case 'lng_ident' :
      case 'lng_short' :
      case 'lng_title' :
        return TRUE;
    }
    return parent::offsetExists($name);
  }
}

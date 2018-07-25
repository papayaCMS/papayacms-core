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

namespace Papaya\Content;

/**
 * Provide a superclass data encapsulation for the content box itself. Here are two children
 * of this class {@see Papaya\Content\Box\Work} for the working copy and
 * {@see Papaya\Content\Box\Publication} for the published version.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
abstract class Box extends \PapayaDatabaseObjectRecord {

  const DELIVERY_MODE_STATIC = 0;
  const DELIVERY_MODE_ESI = 1;
  const DELIVERY_MODE_JAVASCRIPT = 2;

  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    // box id
    'id' => 'box_id',
    // box group id
    'group_id' => 'boxgroup_id',
    // name for administration interface
    'name' => 'box_name',
    // creation / modification timestamps
    'created' => 'box_created',
    'modified' => 'box_modified',
    // delivery mode for box (static, esi, js)
    'delivery_mode' => 'box_deliverymode',
    // server side content caching
    'cache_mode' => 'box_cachemode',
    'cache_time' => 'box_cachetime',
    // browser/proxy caching
    'expires_mode' => 'box_expiresmode',
    'expires_time' => 'box_expirestime',
    // unpublished translations counter
    'unpublished_translations' => 'box_unpublished_languages'
  );

  protected $_tableName = \PapayaContentTables::BOXES;

  /**
   * Box translations list object
   *
   * @var Box\Translations
   */
  protected $_translations = NULL;

  public function load($id) {
    if (parent::load($id)) {
      $this->translations()->load($id);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Access to the translation list information
   *
   * Allows to get/set the list object. Can create a list object if needed.
   *
   * @param Box\Translations $translations
   * @return Box\Translations
   */
  public function translations(Box\Translations $translations = NULL) {
    if (isset($translations)) {
      $this->_translations = $translations;
    }
    if (is_null($this->_translations)) {
      $this->_translations = new Box\Translations();
      $this->_translations->setDatabaseAccess($this->getDatabaseAccess());
    }
    return $this->_translations;
  }
}

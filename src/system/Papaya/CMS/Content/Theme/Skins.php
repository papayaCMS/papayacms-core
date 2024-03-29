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
namespace Papaya\CMS\Content\Theme;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * This object loads the available theme sets into a list.
 *
 * Theme sets are a group of dynamic values that are replaces in the result CSS
 * if the wrapper is used.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Skins extends Database\Records\Lazy {
  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'id' => 'themeset_id',
    'title' => 'themeset_title',
    'theme' => 'theme_name'
  ];

  protected $_identifierProperties = ['id'];

  protected $_orderByFields = [
    'themeset_title' => Database\Interfaces\Order::ASCENDING
  ];

  /**
   * Table containing view information
   *
   * @var string
   */
  protected $_tableName = Content\Tables::THEME_SKINS;
}

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
namespace Papaya\CMS\Content\Community;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * Provide data encapsulation for the surfer permission records.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Permissions extends Database\Records {
  protected $_fields = [
    'id' => 'surferperm_id',
    'title' => 'surferperm_title',
    'active' => 'surferperm_active'
  ];

  protected $_orderByFields = [
    'surferperm_title' => Database\Interfaces\Order::ASCENDING,
    'surferperm_id' => Database\Interfaces\Order::ASCENDING
  ];

  protected $_identifierProperties = 'id';

  protected $_tableName = Content\Tables::COMMUNITY_PERMISSIONS;
}

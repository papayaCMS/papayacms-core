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
namespace Papaya\Content\Authentication;

/**
 * Provide data encapsulation for the administration user group records.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Groups extends \Papaya\Database\Records\Lazy {
  protected $_fields = [
    'id' => 'group_id',
    'title' => 'grouptitle'
  ];

  protected $_orderByFields = [
    'grouptitle' => \Papaya\Database\Interfaces\Order::ASCENDING,
    'group_id' => \Papaya\Database\Interfaces\Order::ASCENDING
  ];

  protected $_identifierProperties = 'id';

  protected $_tableName = \Papaya\Content\Tables::AUTHENTICATION_GROUPS;
}

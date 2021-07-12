<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Content\Authentication {

  use Papaya\CMS\Content;
  use Papaya\Database;

  /**
   * Provide data encapsulation for the administration user group records.
   *
   * @package Papaya-Library
   * @subpackage Content
   *
   * @property int $id
   * @property string $title
   */
  class Group extends Database\Record\Lazy {

    protected $_fields = [
      'id' => 'group_id',
      'title' => 'grouptitle'
    ];

    protected $_identifierProperties = 'id';

    protected $_tableName = Content\Tables::AUTHENTICATION_GROUPS;

  }
}

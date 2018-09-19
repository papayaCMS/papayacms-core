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
namespace Papaya\Content\Page;

/**
 * Load status informations about a page.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $id
 * @property int $sessionMode
 */
class Status extends \Papaya\Database\Record {
  protected $_fields = [
    'id' => 'topic_id',
    'sessionMode' => 'topic_sessionmode'
  ];

  protected $_tableName = \Papaya\Content\Tables::PAGES;
}

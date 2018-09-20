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
namespace Papaya\Content\Phrase;

use Papaya\Database;
use Papaya\Content;

/**
 * Encapsulation for phrase groups, groups allows more efficient loading for phrases
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property string $id
 * @property string $title
 * @property string $identifier
 */
class Group extends Database\Record\Lazy {
  /**
   * Map field names to more convenient property names
   *
   * @var string[]
   */
  protected $_fields = [
    'id' => 'module_id',
    'title' => 'module_title',
    'identifier' => 'module_title_lower'
  ];

  protected $_tableName = Content\Tables::PHRASE_GROUPS;
}

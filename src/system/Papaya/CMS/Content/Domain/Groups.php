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
namespace Papaya\CMS\Content\Domain;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * Data encapsulation for a liust of domain groups
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Groups extends Database\Records\Lazy {
  /**
   * Map field names to more convinient property names
   *
   * @var string[]
   */
  protected $_fields = [
    'id' => 'domaingroup_id',
    'title' => 'domaingroup_title'
  ];

  /**
   * @var string
   */
  protected $_tableName = Content\Tables::DOMAIN_GROUPS;

  protected $_identifierProperties = ['id'];

  /**
   * @param int|array|null $filter
   *
   * @return Group
   */
  public function getItem($filter = NULL) {
    $result = new Group();
    if (\is_scalar($filter)) {
      $filter = ['id' => $filter];
    }
    $result->activateLazyLoad($filter);
    return $result;
  }
}

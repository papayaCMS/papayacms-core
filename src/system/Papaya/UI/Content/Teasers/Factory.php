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
namespace Papaya\UI\Content\Teasers;

use Papaya\Application;
use Papaya\Content;
use Papaya\Database;

/**
 * Create teaser list object including the needed pages database object for it.
 *
 * @package Papaya-Library
 * @subpackage UI-Content
 */
class Factory implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * thumbnail width
   *
   * @var int
   */
  private $_width;

  /**
   * thumbnail height
   *
   * @var int
   */
  private $_height;

  /**
   * thumbnail resize mode (abs, max, min, mincrop)
   *
   * @var int
   */
  private $_resizeMode;

  const ORDER_TITLE_ASCENDING = 'title_asc';

  const ORDER_TITLE_DESCENDING = 'title_desc';

  const ORDER_POSITION_ASCENDING = 'position_asc';

  const ORDER_POSITION_DESCENDING = 'position_desc';

  const ORDER_CREATED_ASCENDING = 'created_asc';

  const ORDER_CREATED_DESCENDING = 'created_desc';

  const ORDER_MODIFIED_ASCENDING = 'modified_asc';

  const ORDER_MODIFIED_DESCENDING = 'modified_desc';

  private $_orderByDefinitions = [
    self::ORDER_TITLE_ASCENDING => [
      'title' => Database\Interfaces\Order::ASCENDING,
      'position' => Database\Interfaces\Order::ASCENDING,
      'created' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_TITLE_DESCENDING => [
      'title' => Database\Interfaces\Order::DESCENDING,
      'position' => Database\Interfaces\Order::ASCENDING,
      'created' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_POSITION_ASCENDING => [
      'position' => Database\Interfaces\Order::ASCENDING,
      'title' => Database\Interfaces\Order::ASCENDING,
      'created' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_POSITION_DESCENDING => [
      'position' => Database\Interfaces\Order::DESCENDING,
      'title' => Database\Interfaces\Order::ASCENDING,
      'created' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_CREATED_ASCENDING => [
      'created' => Database\Interfaces\Order::ASCENDING,
      'title' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_CREATED_DESCENDING => [
      'created' => Database\Interfaces\Order::DESCENDING,
      'title' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_MODIFIED_ASCENDING => [
      'modified' => Database\Interfaces\Order::ASCENDING,
      'title' => Database\Interfaces\Order::ASCENDING
    ],
    self::ORDER_MODIFIED_DESCENDING => [
      'modified' => Database\Interfaces\Order::DESCENDING,
      'title' => Database\Interfaces\Order::ASCENDING
    ]
  ];

  /**
   * Factory constructor.
   *
   * @param int $width
   * @param int $height
   * @param string $resizeMode
   */
  public function __construct($width = 0, $height = 0, $resizeMode = 'mincrop') {
    $this->_width = $width;
    $this->_height = $height;
    $this->_resizeMode = $resizeMode;
  }

  /**
   * Get a teaser list by a defined filter
   *
   * @param array $filter
   * @param string|Database\Interfaces\Order $order
   * @param int $limit
   * @param int $offset
   *
   * @return \Papaya\UI\Content\Teasers
   */
  public function byFilter(
    array $filter, $order = self::ORDER_POSITION_ASCENDING, $limit = 10, $offset = 0
  ) {
    $pages = $this->createPages($order);
    if (!isset($filter['language_id'])) {
      $filter['language_id'] = $this->papaya()->request->languageId;
    }
    if (!isset($filter['viewmode_id'])) {
      $filter['viewmode_id'] = $this->papaya()->request->modeId;
    }
    $pages->activateLazyLoad($filter, $limit, $offset);
    return new \Papaya\UI\Content\Teasers($pages, $this->_width, $this->_height, $this->_resizeMode);
  }

  /**
   * Get a teaser list by one or more parent page ids.
   *
   * @param array(integer)|integer $pageIds
   * @param string|Database\Interfaces\Order $order
   * @param int $limit
   * @param int $offset
   *
   * @return \Papaya\UI\Content\Teasers
   */
  public function byParent(
    $pageIds, $order = self::ORDER_POSITION_ASCENDING, $limit = 10, $offset = 0
  ) {
    return $this->byFilter(['parent' => $pageIds], $order, $limit, $offset);
  }

  /**
   * Get a teaser list by one or more page ids.
   *
   * @param array(integer)|integer $pageIds
   * @param string|Database\Interfaces\Order $order
   * @param int $limit
   * @param int $offset
   *
   * @return \Papaya\UI\Content\Teasers
   */
  public function byPageId(
    $pageIds, $order = self::ORDER_TITLE_ASCENDING, $limit = 10, $offset = 0
  ) {
    return $this->byFilter(['id' => $pageIds], $order, $limit, $offset);
  }

  /**
   * Create a pages database encapsulation object
   *
   * @param string|Database\Interfaces\Order $order
   *
   * @return Content\Pages|Content\Page\Publications
   */
  private function createPages($order) {
    $pages = $this->papaya()->request->isPreview ? new Content\Pages() : new Content\Page\Publications();
    if ($orderBy = $this->getOrderBy($order, $pages)) {
      $pages->orderBy($orderBy);
    }
    $pages->papaya($this->papaya());
    return $pages;
  }

  /**
   * If the $order is already an \Papaya\Database\Interfaces\Order return it. Otherwise
   * check if it is here is an definition in $_orderByDefinitions and us this. If no
   * definition can be found return NULL.
   *
   * @param string|Database\Interfaces\Order $order
   * @param Content\Pages $pages
   *
   * @return Database\Interfaces\Order
   */
  private function getOrderBy($order, Content\Pages $pages) {
    if ($order instanceof Database\Interfaces\Order) {
      return $order;
    }
    if (isset($this->_orderByDefinitions[$order])) {
      return new Database\Record\Order\By\Properties(
        $this->_orderByDefinitions[$order], $pages->mapping()
      );
    }
    return NULL;
  }
}

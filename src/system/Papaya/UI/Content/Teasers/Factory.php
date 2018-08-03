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
/**
 * Create teaser list object including the needed pages database object for it.
 *
 * @package Papaya-Library
 * @subpackage UI-Content
 */
class Factory extends \Papaya\Application\BaseObject {

  /**
   * thumbnail width
   *
   * @var integer
   */
  private $_width = 0;

  /**
   * thumbnail height
   *
   * @var integer
   */
  private $_height = 0;

  /**
   * thumbnail resize mode (abs, max, min, mincrop)
   *
   * @var integer
   */
  private $_resizeMode = 'max';

  const ORDER_TITLE_ASCENDING = 'title_asc';
  const ORDER_TITLE_DESCENDING = 'title_desc';
  const ORDER_POSITION_ASCENDING = 'position_asc';
  const ORDER_POSITION_DESCENDING = 'position_desc';
  const ORDER_CREATED_ASCENDING = 'created_asc';
  const ORDER_CREATED_DESCENDING = 'created_desc';
  const ORDER_MODIFIED_ASCENDING = 'modified_asc';
  const ORDER_MODIFIED_DESCENDING = 'modified_desc';

  private $_orderByDefinitions = array(
    self::ORDER_TITLE_ASCENDING => array(
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'position' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'created' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_TITLE_DESCENDING => array(
      'title' => \Papaya\Database\Interfaces\Order::DESCENDING,
      'position' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'created' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_POSITION_ASCENDING => array(
      'position' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'created' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_POSITION_DESCENDING => array(
      'position' => \Papaya\Database\Interfaces\Order::DESCENDING,
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'created' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_CREATED_ASCENDING => array(
      'created' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_CREATED_DESCENDING => array(
      'created' => \Papaya\Database\Interfaces\Order::DESCENDING,
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_MODIFIED_ASCENDING => array(
      'modified' => \Papaya\Database\Interfaces\Order::ASCENDING,
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING
    ),
    self::ORDER_MODIFIED_DESCENDING => array(
      'modified' => \Papaya\Database\Interfaces\Order::DESCENDING,
      'title' => \Papaya\Database\Interfaces\Order::ASCENDING
    )
  );

  public function __construct($width = 0, $height = 0, $resizeMode = 'mincrop') {
    $this->_width = $width;
    $this->_height = $height;
    $this->_resizeMode = $resizeMode;
  }


  /**
   * Get a teaser list by a defined filter
   *
   * @param array $filter
   * @param string|\Papaya\Database\Interfaces\Order $order
   * @param integer $limit
   * @param integer $offset
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
   * @param string|\Papaya\Database\Interfaces\Order $order
   * @param integer $limit
   * @param integer $offset
   * @return \Papaya\UI\Content\Teasers
   */
  public function byParent(
    $pageIds, $order = self::ORDER_POSITION_ASCENDING, $limit = 10, $offset = 0
  ) {
    return $this->byFilter(array('parent' => $pageIds), $order, $limit, $offset);
  }

  /**
   * Get a teaser list by one or more page ids.
   *
   * @param array(integer)|integer $pageIds
   * @param string|\Papaya\Database\Interfaces\Order $order
   * @param integer $limit
   * @param integer $offset
   * @return \Papaya\UI\Content\Teasers
   */
  public function byPageId(
    $pageIds, $order = self::ORDER_TITLE_ASCENDING, $limit = 10, $offset = 0
  ) {
    return $this->byFilter(array('id' => $pageIds), $order, $limit, $offset);
  }

  /**
   * Create a pages database encapsulation object
   *
   * @param string|\Papaya\Database\Interfaces\Order $order
   * @return \Papaya\Content\Pages|\Papaya\Content\Pages\Publications
   */
  private function createPages($order) {
    if ($this->papaya()->request->isPreview) {
      $pages = new \Papaya\Content\Pages();
    } else {
      $pages = new \Papaya\Content\Pages\Publications();
    }
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
   * @param string|\Papaya\Database\Interfaces\Order $order
   * @param \Papaya\Content\Pages $pages
   * @return \Papaya\Database\Interfaces\Order
   */
  private function getOrderBy($order, \Papaya\Content\Pages $pages) {
    if ($order instanceof \Papaya\Database\Interfaces\Order) {
      return $order;
    } elseif (isset($this->_orderByDefinitions[$order])) {
      return new \Papaya\Database\Record\Order\By\Properties(
        $this->_orderByDefinitions[$order], $pages->mapping()
      );
    }
    return NULL;
  }

}

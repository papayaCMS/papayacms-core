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
namespace Papaya\UI\ListView\Item;

/**
 * Provides several links to navigate mutiple pages of a list in a listview.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\Reference $reference
 * @property string|array $parameterName
 * @property int $currentPage
 * @property int $currentOffset
 * @property int $lastPage
 * @property int $itemsCount
 * @property int $itemsPerPage
 * @property int $pageLimit
 * @property string $separator
 * @property string $image
 * @property int $indentation
 * @property int $columnSpan
 * @property bool $selected
 */
abstract class Paging extends \Papaya\UI\ListView\Item {
  const MODE_PAGE = 1;

  const MODE_OFFSET = 2;

  protected $_mode = 0;

  protected $_itemsCount = 0;

  protected $_itemsPerPage = 10;

  protected $_currentPage = 0;

  protected $_pageLimit = 3;

  protected $_parameterName = 'page';

  protected $_image = 'items-table';

  protected $_separator = '';

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'subitems' => ['subitems', 'subitems'],
    'image' => ['_image', '_image'],
    'selected' => ['_selected', '_selected'],
    'indentation' => ['_indentation', 'setIndentation'],
    'columnSpan' => ['_columnSpan', '_columnSpan'],
    'reference' => ['reference', 'reference'],
    'parameterName' => ['_parameterName', '_parameterName'],
    'currentPage' => ['getCurrentPage', 'setCurrentPage'],
    'currentOffset' => ['getCurrentOffset', 'setCurrentOffset'],
    'lastPage' => ['getLastPage'],
    'itemsCount' => ['_itemsCount', 'setItemsCount'],
    'itemsPerPage' => ['_itemsPerPage', 'setItemsPerPage'],
    'pageLimit' => ['_pageLimit', 'setPageLimit'],
    'separator' => ['_separator', '_separator'],
  ];

  /**
   * Create object and store properties
   *
   * @param string|array $parameterName
   * @param int $currentValue
   * @param int $itemsCount
   * @param int $mode
   */
  public function __construct($parameterName, $currentValue, $itemsCount, $mode = self::MODE_PAGE) {
    $this->_parameterName = new \Papaya\Request\Parameters\Name($parameterName);
    $this->_mode = $mode;
    $this->setItemsCount($itemsCount);
    $this->setCurrentValue($currentValue);
  }

  /**
   * This method calculates and returns and array of page numbers.
   *
   * @return array
   */
  abstract public function getPages();

  /**
   * Return the page that will be used for the image link
   *
   * @return int
   */
  abstract public function getImagePage();

  /**
   * Append the listitem to the listview. The list item will only be added, if it contains page
   * links.
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return null|\Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $pages = $this->getPages();
    if (\count($pages) > 0) {
      $page = $this->getImagePage();
      $reference = clone $this->reference();
      $reference->getParameters()->set(
        (string)$this->_parameterName,
        (self::MODE_OFFSET == $this->_mode) ? ($page - 1) * $this->_itemsPerPage : $page
      );
      $itemNode = $parent->appendElement(
        'listitem',
        [
          'image' => $this->papaya()->images[(string)$this->_image],
          'href' => $reference->getRelative()
        ]
      );
      if (0 != $this->_columnSpan) {
        $itemNode->setAttribute('span', $this->getColumnSpan());
      }
      if ((bool)$this->_selected) {
        $itemNode->setAttribute('selected', 'selected');
      }
      $this->appendCaption($itemNode);
      $this->subitems()->appendTo($itemNode);
      return $itemNode;
    }
    return;
  }

  /**
   * The item needs an complex caption containing mutiple links, instead of the usual title
   * attribute and caption element is added.
   *
   * @param \Papaya\XML\Element $item
   */
  public function appendCaption(\Papaya\XML\Element $item) {
    $caption = $item->appendElement(
      'caption'
    );
    $addSeparator = FALSE;
    foreach ($this->getPages() as $page) {
      if ($addSeparator) {
        $caption->appendText($this->_separator);
      }
      $this->appendPageLink($caption, $page);
      $addSeparator = TRUE;
    }
  }

  /**
   * Append a single page link to the caption xml element.
   *
   * @param \Papaya\XML\Element $parent
   * @param int $page
   */
  public function appendPageLink(\Papaya\XML\Element $parent, $page) {
    $reference = clone $this->reference();
    $reference->getParameters()->set(
      (string)$this->_parameterName,
      (self::MODE_OFFSET == $this->_mode) ? ($page - 1) * $this->_itemsPerPage : $page
    );
    $parent->appendElement(
      'a', ['href' => $reference->getRelative()], $page
    );
  }

  /**
   * The absolute count of items in the list. The minimum value is zero.
   *
   * @param int $itemsCount
   *
   * @throws \UnexpectedValueException
   */
  public function setItemsCount($itemsCount) {
    \Papaya\Utility\Constraints::assertInteger($itemsCount);
    if ($itemsCount < 0) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Item count can not be negative.'
      );
    }
    $this->_itemsCount = $itemsCount;
  }

  /**
   * The maximum count of items on one page. The last page can contain less items. The
   * minimum value is 1.
   *
   * @param int $itemsPerPage
   *
   * @throws \UnexpectedValueException
   */
  public function setItemsPerPage($itemsPerPage) {
    \Papaya\Utility\Constraints::assertInteger($itemsPerPage);
    if ($itemsPerPage < 1) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Item page limit can not be less than 1.'
      );
    }
    $this->_itemsPerPage = $itemsPerPage;
  }

  /**
   * The maximum count of page links.
   *
   * @param $pageLimit
   *
   * @throws \UnexpectedValueException
   *
   * @internal param int $buttonLimit
   */
  public function setPageLimit($pageLimit) {
    \Papaya\Utility\Constraints::assertInteger($pageLimit);
    if ($pageLimit < 1) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Page limit can not be less than 1.'
      );
    }
    $this->_pageLimit = $pageLimit;
  }

  /**
   * Depending on the mode set the current value as page or as offset value.
   *
   * @param $currentValue
   *
   * @return int
   */
  public function setCurrentValue($currentValue) {
    switch ($this->_mode) {
      case self::MODE_OFFSET :
        $this->setCurrentOffset($currentValue);
      break;
      default :
        $this->setCurrentPage($currentValue);
      break;
    }
  }

  /**
   * Set the current page value, fix it if it is outside the possible values.
   * The page value is based one 1.
   *
   * @return int
   */
  public function getCurrentPage() {
    $lastPage = $this->getLastPage();
    if ($this->_currentPage > $lastPage) {
      $this->_currentPage = $lastPage;
    }
    if ($this->_currentPage < 1) {
      $this->_currentPage = 1;
    }
    return $this->_currentPage;
  }

  /**
   * Change the current page. This will reset the current caclulation results.
   *
   * @param int $page
   */
  public function setCurrentPage($page) {
    $this->_currentPage = $page;
  }

  /**
   * Return the current offset. This is an alternative represenation of the current page. It
   * is the index (based on zero) of the first item on this page.
   *
   * @return int
   */
  public function getCurrentOffset() {
    return ($this->getCurrentPage() - 1) * $this->_itemsPerPage;
  }

  /**
   * Set the current page using an offset. The offset it the index of the first item (bases on zero)
   * on a page.
   *
   * @param int $offset
   */
  public function setCurrentOffset($offset) {
    $this->setCurrentPage(\floor($offset / $this->_itemsPerPage) + 1);
  }

  /**
   * Return the last possible page depending on the item count.
   *
   * @return int
   */
  public function getLastPage() {
    return (int)\ceil($this->_itemsCount / $this->_itemsPerPage);
  }
}

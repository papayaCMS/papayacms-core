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

/**
* Provides several links to navigate mutiple pages of a list in a listview.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property \PapayaUiReference $reference
* @property string|array $parameterName
* @property integer $currentPage
* @property integer $currentOffset
* @property integer $lastPage
* @property integer $itemsCount
* @property integer $itemsPerPage
* @property integer $pageLimit
* @property string $separator
* @property string $image
* @property integer $indentation
* @property integer $columnSpan
* @property boolean $selected
*/
abstract class PapayaUiListviewItemPaging extends \PapayaUiListviewItem {

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
  protected $_declaredProperties = array(
    'subitems' => array('subitems', 'subitems'),
    'image' => array('_image', '_image'),
    'selected' => array('_selected', '_selected'),
    'indentation' => array('_indentation', 'setIndentation'),
    'columnSpan' => array('_columnSpan', '_columnSpan'),
    'reference' => array('reference', 'reference'),
    'parameterName' => array('_parameterName', '_parameterName'),
    'currentPage' => array('getCurrentPage', 'setCurrentPage'),
    'currentOffset' => array('getCurrentOffset', 'setCurrentOffset'),
    'lastPage' => array('getLastPage'),
    'itemsCount' => array('_itemsCount', 'setItemsCount'),
    'itemsPerPage' => array('_itemsPerPage', 'setItemsPerPage'),
    'pageLimit' => array('_pageLimit', 'setPageLimit'),
    'separator' => array('_separator', '_separator'),
  );

  /**
  * Create object and store properties
  *
  * @param string|array $parameterName
  * @param integer $currentValue
  * @param integer $itemsCount
  * @param integer $mode
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
  abstract function getPages();

  /**
  * Return the page that will be used for the image link
  *
  * @return integer
  */
  abstract function getImagePage();

  /**
  * Append the listitem to the listview. The list item will only be added, if it contains page
  * links.
  *
  * @param \PapayaXmlElement $parent
  * @return NULL|\PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $pages = $this->getPages();
    if (count($pages) > 0) {
      $page = $this->getImagePage();
      $reference = clone $this->reference();
      $reference->getParameters()->set(
        (string)$this->_parameterName,
        ($this->_mode == self::MODE_OFFSET) ? ($page - 1) * $this->_itemsPerPage : $page
      );
      $itemNode = $parent->appendElement(
        'listitem',
        array(
          'image' => $this->papaya()->images[(string)$this->_image],
          'href' => $reference->getRelative()
        )
      );
      if ($this->_columnSpan != 0) {
        $itemNode->setAttribute('span', $this->getColumnSpan());
      }
      if ((bool)$this->_selected) {
        $itemNode->setAttribute('selected', 'selected');
      }
      $this->appendCaption($itemNode);
      $this->subitems()->appendTo($itemNode);
      return $itemNode;
    }
    return NULL;
  }

  /**
  * The item needs an complex caption containing mutiple links, instead of the usual title
  * attribute and caption element is added.
  *
  * @param \PapayaXmlElement $item
  */
  public function appendCaption(\PapayaXmlElement $item) {
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
  * @param \PapayaXmlElement $parent
  * @param integer $page
  */
  public function appendPageLink(\PapayaXmlElement $parent, $page) {
    $reference = clone $this->reference();
    $reference->getParameters()->set(
      (string)$this->_parameterName,
      ($this->_mode == self::MODE_OFFSET) ? ($page - 1) * $this->_itemsPerPage : $page
    );
    $parent->appendElement(
      'a', array('href' => $reference->getRelative()), $page
    );
  }

  /**
   * The absolute count of items in the list. The minimum value is zero.
   *
   * @param integer $itemsCount
   * @throws \UnexpectedValueException
   */
  public function setItemsCount($itemsCount) {
    \PapayaUtilConstraints::assertInteger($itemsCount);
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
   * @param integer $itemsPerPage
   * @throws \UnexpectedValueException
   */
  public function setItemsPerPage($itemsPerPage) {
    \PapayaUtilConstraints::assertInteger($itemsPerPage);
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
   * @throws \UnexpectedValueException
   * @internal param int $buttonLimit
   */
  public function setPageLimit($pageLimit) {
    \PapayaUtilConstraints::assertInteger($pageLimit);
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
   * @return integer
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
  * @return integer
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
  * @return integer
  */
  public function getCurrentOffset() {
    return ($this->getCurrentPage() - 1) * $this->_itemsPerPage;
  }

  /**
  * Set the current page using an offset. The offset it the index of the first item (bases on zero)
  * on a page.
  *
  * @param integer $offset
  */
  public function setCurrentOffset($offset) {
    $this->setCurrentPage(floor($offset / $this->_itemsPerPage) + 1);
  }

  /**
  * Return the last possible page depending on the item count.
  *
  * @return integer
  */
  public function getLastPage() {
    return (int)ceil($this->_itemsCount / $this->_itemsPerPage);
  }
}

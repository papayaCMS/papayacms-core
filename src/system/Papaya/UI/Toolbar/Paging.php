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
namespace Papaya\UI\Toolbar;

/**
 * Provides several buttons to navigate mutiple pages of a list.
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
 * @property int $buttonLimit
 */
class Paging extends Element {
  const MODE_PAGE = 0;

  const MODE_OFFSET = 1;

  protected $_mode = self::MODE_PAGE;

  /**
   * Limits the maximum count of page buttons. First/Last and Previous/Next are not included.
   * The minimum value is 3.
   *
   * @var int
   */
  protected $_buttonLimit = 11;

  /**
   * The maximum items on one page. The last page can contain less items.
   * The minimum value is 1.
   *
   * @var int
   */
  protected $_itemsPerPage = 10;

  /**
   * The actual item count. If the value is less than 1 the buttons are hidden.
   *
   * @var int
   */
  protected $_itemsCount = 0;

  /**
   * The parameter name of the page parameter for the links
   *
   * @var string|array
   */
  protected $_parameterName;

  /**
   * The current page number. Minimum and default value is 1.
   *
   * @var int|null
   */
  protected $_currentPage;

  /**
   * The minimum page value. This is caluclated using the button limit.
   * It changes with the current page.
   *
   * @var int|null
   */
  private $_minimumPage;

  /**
   * The maximum page value. This is caluclated using the button limit.
   * It changes with the current page.
   *
   * @var int|null
   */
  private $_maximumPage;

  /**
   * Current page minus 1.
   *
   * @var int|null
   */
  private $_previousPage;

  /**
   * Current page plus 1.
   *
   * @var int|null
   */
  private $_nextPage;

  /**
   * Last possible page value.
   *
   * @var int|null
   */
  private $_lastPage;

  /**
   * Declare public properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'reference' => ['reference', 'reference'],
    'parameterName' => ['_parameterName', 'parameterName'],
    'currentPage' => ['getCurrentPage', 'setCurrentPage'],
    'currentOffset' => ['getCurrentOffset', 'setCurrentOffset'],
    'lastPage' => ['getLastPage'],
    'itemsCount' => ['_itemsCount', 'setItemsCount'],
    'itemsPerPage' => ['_itemsPerPage', 'setItemsPerPage'],
    'buttonLimit' => ['_buttonLimit', 'setButtonLimit'],
  ];

  /**
   * Create object and store parameter name and items count.
   *
   * @param string|array $parameterName
   * @param int $itemsCount
   * @param int $mode
   */
  public function __construct($parameterName, $itemsCount, $mode = self::MODE_PAGE) {
    $this->_parameterName = new \Papaya\Request\Parameters\Name($parameterName);
    $this->setItemsCount($itemsCount);
    $this->_mode = $mode;
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
    $this->reset();
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
    $this->reset();
  }

  /**
   * The maximum count of page button without first, previous, next and last. The
   * Minimum value is 3. I suggest only odd values for this option.
   *
   * @param int $buttonLimit
   *
   * @throws \UnexpectedValueException
   */
  public function setButtonLimit($buttonLimit) {
    \Papaya\Utility\Constraints::assertInteger($buttonLimit);
    if ($buttonLimit < 3) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Button limit can not be less than 3.'
      );
    }
    $this->_buttonLimit = $buttonLimit;
    $this->reset();
  }

  /**
   * Fetch the value from the request and trigger the calculation if needed. Return the current page.
   * The page value is based one 1.
   *
   * @return int
   */
  public function getCurrentPage() {
    $this->getCurrentPageParameter(TRUE);
    if (\is_null($this->_lastPage)) {
      $this->calculate();
    }
    return $this->_currentPage;
  }

  /**
   * Fetch the current page parameter from request or property. This will not trigger the
   * caluclation.
   *
   * @return int
   */
  private function getCurrentPageParameter() {
    if (\is_null($this->_currentPage)) {
      switch ($this->_mode) {
        case self::MODE_OFFSET :
          $this->setCurrentOffset(
            $this->papaya()->request->getParameter(
              (string)$this->_parameterName, 0, new \Papaya\Filter\IntegerValue(0)
            )
          );
        break;
        default :
          $this->setCurrentPage(
            $this->papaya()->request->getParameter(
              (string)$this->_parameterName, 1, new \Papaya\Filter\IntegerValue(1)
            )
          );
        break;
      }
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
    $this->reset();
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
    $this->reset();
  }

  /**
   * Append button xml to parent node. If the item count is zero no button are added.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    if ($this->_itemsCount > $this->_itemsPerPage) {
      $current = $this->getCurrentPage();
      if ($current > 2) {
        $this->appendArrowButton(
          $parent, 1, 'actions-go-first', new \Papaya\UI\Text\Translated('First page')
        );
      }
      if ($current > 1) {
        $this->appendArrowButton(
          $parent,
          $current - 1,
          'actions-go-previous',
          new \Papaya\UI\Text\Translated('Previous page')
        );
      }
      for ($page = $this->_minimumPage; $page <= $this->_maximumPage; ++$page) {
        $reference = clone $this->reference();
        $reference->getParameters()->set(
          (string)$this->_parameterName,
          $this->preparePagingParameter($page)
        );
        $button = $parent->appendElement(
          'button',
          [
            'title' => $page,
            'href' => $reference->getRelative()
          ]
        );
        if ($page == $current) {
          $button->setAttribute('down', 'down');
        }
      }
      if ($current < $this->_lastPage) {
        $this->appendArrowButton(
          $parent, $current + 1, 'actions-go-next', new \Papaya\UI\Text\Translated('Next page')
        );
      }
      if ($current < $this->_lastPage - 1) {
        $this->appendArrowButton(
          $parent, $this->_lastPage, 'actions-go-last', new \Papaya\UI\Text\Translated('Last page')
        );
      }
    }
  }

  /**
   * If offset mode is used convert page to offset.
   *
   * @param int $page
   *
   * @return int
   */
  private function preparePagingParameter($page) {
    switch ($this->_mode) {
      case self::MODE_OFFSET :
        return ($page - 1) * $this->_itemsPerPage;
      default :
        return $page;
    }
  }

  /**
   * Append an arrow button to the parent. Arrow buttons navigate to the first/last or previous/next
   * page. The are only shown if needed.
   *
   * @param \Papaya\XML\Element $parent
   * @param int $page
   * @param string $image
   * @param string|\Papaya\UI\Text $hint
   */
  private function appendArrowButton(\Papaya\XML\Element $parent, $page, $image, $hint) {
    $reference = clone $this->reference();
    $reference->getParameters()->set(
      (string)$this->_parameterName,
      $this->preparePagingParameter($page)
    );
    $parent->appendElement(
      'button',
      [
        'glyph' => $this->papaya()->images[(string)$image],
        'hint' => (string)$hint,
        'href' => $reference->getRelative()
      ]
    );
  }

  /**
   * Resets the internal calculation result to NULL. This way they are caluclated again if needed.
   */
  private function reset() {
    $this->_minimumPage = NULL;
    $this->_maximumPage = NULL;
    $this->_previousPage = NULL;
    $this->_nextPage = NULL;
    $this->_lastPage = NULL;
  }

  /**
   * Calculate sevaral internal limits for the button output.
   */
  private function calculate() {
    $currentPage = $this->getCurrentPageParameter();
    if ($currentPage < 1) {
      $currentPage = 1;
    }
    $lastPage = $this->getLastPage();
    if ($currentPage > $lastPage) {
      $currentPage = $lastPage;
    }
    $minimumPage = $currentPage - (int)\floor($this->_buttonLimit / 2);
    if ($minimumPage + $this->_buttonLimit > $lastPage) {
      $minimumPage = $lastPage - $this->_buttonLimit + 1;
    }
    if ($minimumPage < 1) {
      $minimumPage = 1;
    }
    $maximumPage = $minimumPage + $this->_buttonLimit;
    if ($maximumPage > $lastPage) {
      $maximumPage = $lastPage;
    }
    $this->_currentPage = $currentPage;
    $this->_minimumPage = $minimumPage;
    $this->_maximumPage = $maximumPage;
    $this->_previousPage = ($currentPage > 1) ? $currentPage - 1 : 0;
    $this->_nextPage = ($currentPage >= $lastPage) ? $lastPage : $currentPage + 1;
    $this->_lastPage = $lastPage;
  }

  /**
   * Get the last possible page number.
   *
   * @return int
   */
  public function getLastPage() {
    return (int)\ceil($this->_itemsCount / $this->_itemsPerPage);
  }
}

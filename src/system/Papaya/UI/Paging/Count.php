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
namespace Papaya\UI\Paging;

use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * Output paging links based on a item count.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property UI\Reference $reference
 * @property string|array $parameterName
 * @property int $currentPage
 * @property int $lastPage
 * @property int $itemsCount
 * @property int $itemsPerPage
 * @property int $pageLimit
 */
class Count extends UI\Control {
  const LINK_FIRST = 'first';

  const LINK_PREVIOUS = 'previous';

  const LINK_NEXT = 'next';

  const LINK_LAST = 'last';

  const LINK_SELECTED = 'selected';

  /**
   * reference (link) object
   *
   * @var UI\Reference
   */
  protected $_reference;

  /**
   * The parameter name of the page parameter for the links
   *
   * @var string|array
   */
  protected $_parameterName = 'page';

  /**
   * Limits the maximum count of page buttons. First/Last and Previous/Next are not included.
   * The minimum value is 3.
   *
   * @var int
   */
  protected $_pageLimit = 11;

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
   * The current page number. Minimum and default value is 1.
   *
   * @var int|null
   */
  protected $_currentPage;

  /**
   * The minimum page value. This is calculated using the button limit.
   * It changes with the current page.
   *
   * @var int|null
   */
  private $_minimumPage;

  /**
   * The maximum page value. This is calculated using the button limit.
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
   * Calculation status, allows to recalculated only if needed
   *
   * @var bool
   */
  private $_calculated = FALSE;

  /**
   * Declare public properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'reference' => ['reference', 'reference'],
    'parameterName' => ['_parameterName', '_parameterName'],
    'currentPage' => ['getCurrentPage', 'setCurrentPage'],
    'lastPage' => ['getLastPage'],
    'itemsCount' => ['_itemsCount', 'setItemsCount'],
    'itemsPerPage' => ['_itemsPerPage', 'setItemsPerPage'],
    'pageLimit' => ['_pageLimit', 'setPageLimit'],
  ];

  /**
   * The xml names allow to define the element and attribute names of the generated xml
   *
   * @var array
   */
  protected $_xmlNames = [
    'list' => 'paging',
    'attr-count' => 'count',
    'item' => 'page',
    'attr-href' => 'href',
    'attr-page' => 'number',
    'attr-type' => 'type',
    'attr-selected' => 'selected'
  ];

  /**
   * Create object and store needed data.
   *
   * @param string|array|\Papaya\Request\Parameters\Name $parameterName
   * @param int $currentPage
   * @param int $itemsCount
   */
  public function __construct($parameterName, $currentPage, $itemsCount) {
    $this->_parameterName = $parameterName;
    $this->_currentPage = $currentPage;
    $this->_itemsCount = $itemsCount;
  }

  /**
   * Allow to specify element and attribute names for the generated xml
   *
   * @param array $names
   *
   * @throws \UnexpectedValueException
   */
  public function setXMLNames(array $names) {
    foreach ($names as $element => $name) {
      if (\array_key_exists($element, $this->_xmlNames) &&
        \preg_match('(^[a-z][a-z_\d-]*$)Di', $name)) {
        $this->_xmlNames[$element] = $name;
      } else {
        throw new \UnexpectedValueException(
          \sprintf(
            'Invalid/unknown xml name element "%s" with value "%s".',
            $element,
            $name
          )
        );
      }
    }
  }

  /**
   * Append a list of paging links to the parent.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $this->calculate();
    if ($this->_itemsCount > $this->_itemsPerPage) {
      $list = $this->appendListElement($parent);
      $current = $this->getCurrentPage();
      if ($current > 2) {
        $this->appendPageElement($list, 1, self::LINK_FIRST);
      }
      if ($current > 1) {
        $this->appendPageElement($list, $current - 1, self::LINK_PREVIOUS);
      }
      for ($page = $this->_minimumPage; $page <= $this->_maximumPage; ++$page) {
        $this->appendPageElement($list, $page, $page === $current ? self::LINK_SELECTED : NULL);
      }
      if ($current < $this->_lastPage) {
        $this->appendPageElement($list, $current + 1, self::LINK_NEXT);
      }
      if ($current < $this->_lastPage - 1) {
        $this->appendPageElement($list, $this->_lastPage, self::LINK_LAST);
      }
    }
  }

  /**
   * Append the list element to the xml
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  protected function appendListElement(XML\Element $parent) {
    return $parent->appendElement(
      $this->_xmlNames['list'], [$this->_xmlNames['attr-count'] => $this->getLastPage()]
    );
  }

  /**
   * Append one paging link xml element to the list
   *
   * @param XML\Element $parent
   * @param int $page
   * @param string|null $type
   *
   * @return XML\Element
   */
  protected function appendPageElement(XML\Element $parent, $page, $type = NULL) {
    $reference = clone $this->reference();
    $reference->getParameters()->set(
      (string)$this->_parameterName,
      $page
    );
    $item = $parent->appendElement(
      $this->_xmlNames['item'],
      [
        $this->_xmlNames['attr-href'] => $reference->getRelative(),
        $this->_xmlNames['attr-page'] => $page
      ]
    );
    if (self::LINK_SELECTED === $type) {
      $item->setAttribute($this->_xmlNames['attr-selected'], $this->_xmlNames['attr-selected']);
    } elseif ('' !== \trim($type)) {
      $item->setAttribute($this->_xmlNames['attr-type'], $type);
    }
    return $item;
  }

  /**
   * Getter/Setter for the reference object (the link url)
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new UI\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * The absolute count of items in the list. The minimum value is zero.
   *
   * @param int $itemsCount
   *
   * @throws \UnexpectedValueException
   */
  public function setItemsCount($itemsCount) {
    Utility\Constraints::assertInteger($itemsCount);
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
    Utility\Constraints::assertInteger($itemsPerPage);
    if ($itemsPerPage < 1) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Item page limit can not be less than 1.'
      );
    }
    $this->_itemsPerPage = $itemsPerPage;
    $this->reset();
  }

  /**
   * The maximum count of page links without first, previous, next and last. The
   * Minimum value is 3. I suggest only odd values for this option.
   *
   * @param int $pageLimit
   *
   * @throws \UnexpectedValueException
   */
  public function setPageLimit($pageLimit) {
    Utility\Constraints::assertInteger($pageLimit);
    if ($pageLimit < 3) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Page limit can not be less than 3.'
      );
    }
    $this->_pageLimit = $pageLimit;
    $this->reset();
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
   * Fetch the value from the request and trigger the calculation if needed. Return the current page.
   * The page value is based one 1.
   *
   * @return int
   */
  public function getCurrentPage() {
    $this->calculate();
    return $this->_currentPage;
  }

  public function isCalculated(): bool {
    return $this->_calculated;
  }

  /**
   * Resets the internal calculation result to NULL. This way they are calculated again if needed.
   */
  private function reset() {
    $this->_calculated = FALSE;
  }

  /**
   * Calculate several internal limits for the button output.
   */
  private function calculate() {
    if ($this->_calculated) {
      return;
    }
    $currentPage = $this->_currentPage;
    if ($currentPage < 1) {
      $currentPage = 1;
    }
    $lastPage = $this->getLastPage();
    if ($currentPage > $lastPage) {
      $currentPage = $lastPage;
    }
    $minimumPage = $currentPage - (int)\floor($this->_pageLimit / 2);
    if ($minimumPage + $this->_pageLimit > $lastPage) {
      $minimumPage = $lastPage - $this->_pageLimit + 1;
    }
    if ($minimumPage < 1) {
      $minimumPage = 1;
    }
    $maximumPage = $minimumPage + $this->_pageLimit - 1;
    if ($maximumPage > $lastPage) {
      $maximumPage = $lastPage;
    }
    $this->_currentPage = $currentPage;
    $this->_minimumPage = $minimumPage;
    $this->_maximumPage = $maximumPage;
    $this->_previousPage = ($currentPage > 1) ? $currentPage - 1 : 0;
    $this->_nextPage = ($currentPage >= $lastPage) ? $lastPage : $currentPage + 1;
    $this->_lastPage = $lastPage;
    $this->_calculated = TRUE;
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

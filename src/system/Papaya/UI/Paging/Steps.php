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

/**
 * Output paging steps size links based on a list.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\Reference $reference
 * @property string|array $parameterName
 * @property int $currentStepSize
 * @property array|\Traversable $stepSizes
 * @property int $itemsCount
 * @property int $itemsPerPage
 * @property int $pageLimit
 */
class Steps extends \Papaya\UI\Control {
  const USE_KEYS = 0;

  const USE_VALUES = 1;

  private $_reference;

  private $_stepSizes = [];

  private $_mode = self::USE_VALUES;

  /**
   * The parameter name of the step size parameter for the links
   *
   * @var string|array
   */
  protected $_parameterName = 'page';

  /**
   * The current step size
   *
   * @var string|int
   */
  protected $_currentStepSize = 0;

  /**
   * The xml names allow to define the element and attribute names of the generated xml
   *
   * @var array
   */
  protected $_xmlNames = [
    'list' => 'paging-steps',
    'item' => 'step-size',
    'attr-href' => 'href',
    'attr-selected' => 'selected'
  ];

  /**
   * Declare public properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'reference' => ['reference', 'reference'],
    'parameterName' => ['_parameterName', '_parameterName'],
    'currentStepSize' => ['_currentStepSize', '_currentStepSize'],
    'stepSizes' => ['getStepSizes', 'setStepSizes'],
    'mode' => ['_mode', '_mode']
  ];

  /**
   * create object, stores stepSizes list and mode
   *
   *
   * @param string $parameterName
   * @param string|int $currentStepSize
   * @param \Traversable|array $stepSizes
   */
  public function __construct($parameterName, $currentStepSize, $stepSizes) {
    $this->_parameterName = $parameterName;
    $this->_currentStepSize = $currentStepSize;
    $this->setStepSizes($stepSizes);
  }

  /**
   * Append stepSize elements top parent xml element
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $list = $parent->appendElement($this->_xmlNames['list']);
    foreach ($this->getStepSizes() as $key => $stepSize) {
      $parameterValue = self::USE_KEYS == $this->_mode ? $key : (string)$stepSize;
      $reference = clone $this->reference();
      $reference->getParameters()->set($this->_parameterName, $parameterValue);
      $stepSizeNode = $list->appendElement(
        $this->_xmlNames['item'],
        [
          $this->_xmlNames['attr-href'] => $reference->getRelative()
        ],
        (string)$stepSize
      );
      if ($parameterValue == $this->_currentStepSize) {
        $stepSizeNode->setAttribute(
          $this->_xmlNames['attr-selected'], $this->_xmlNames['attr-selected']
        );
      }
    }
    return $list;
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
   * Store the stepSizes list
   *
   * @param \Traversable|array $stepSizes
   */
  public function setStepSizes($stepSizes) {
    \Papaya\Utility\Constraints::assertArrayOrTraversable($stepSizes);
    $this->_stepSizes = $stepSizes;
  }

  /**
   * Return the stepSizes list
   *
   * @return \Traversable|array
   */
  public function getStepSizes() {
    return $this->_stepSizes;
  }

  /**
   * Getter/Setter for the reference subobject.
   *
   * @param \Papaya\UI\Reference $reference
   *
   * @return null|\Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (\is_null($this->_reference)) {
      $this->_reference = new \Papaya\UI\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}

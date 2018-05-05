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
* Output paging steps size links based on a list.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiReference $reference
* @property string|array $parameterName
* @property integer $currentStepSize
* @property array|Traversable $stepSizes
* @property integer $itemsCount
* @property integer $itemsPerPage
* @property integer $pageLimit
*/
class PapayaUiPagingSteps extends \PapayaUiControl {

  const USE_KEYS = 0;
  const USE_VALUES = 1;

  private $_reference = NULL;
  private $_stepSizes = array();
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
  * @var string|integer
  */
  protected $_currentStepSize = 0;

  /**
  * The xml names allow to define the element and attribute names of the generated xml
  *
  * @var array
  */
  protected $_xmlNames = array(
    'list' => 'paging-steps',
    'item' => 'step-size',
    'attr-href' => 'href',
    'attr-selected' => 'selected'
  );

  /**
  * Declare public properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'reference' => array('reference', 'reference'),
    'parameterName' => array('_parameterName', '_parameterName'),
    'currentStepSize' => array('_currentStepSize', '_currentStepSize'),
    'stepSizes' => array('getStepSizes', 'setStepSizes'),
    'mode' => array('_mode', '_mode')
  );

  /**
   * create object, stores stepSizes list and mode
   *
   *
   * @param string $parameterName
   * @param string|integer $currentStepSize
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
  * @param \PapayaXmlElement $parent
  * @return \PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $list = $parent->appendElement($this->_xmlNames['list']);
    foreach ($this->getStepSizes() as $key => $stepSize) {
      $parameterValue = $this->_mode == self::USE_KEYS ? $key : (string)$stepSize;
      $reference = clone $this->reference();
      $reference->getParameters()->set($this->_parameterName, $parameterValue);
      $stepSizeNode = $list->appendElement(
        $this->_xmlNames['item'],
        array(
          $this->_xmlNames['attr-href'] => $reference->getRelative()
        ),
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
   * @throws \UnexpectedValueException
   */
  public function setXmlNames(array $names) {
    foreach ($names as $element => $name) {
      if (array_key_exists($element, $this->_xmlNames) &&
          preg_match('(^[a-z][a-z_\d-]*$)Di', $name)) {
        $this->_xmlNames[$element] = $name;
      } else {
        throw new \UnexpectedValueException(
          sprintf(
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
    \PapayaUtilConstraints::assertArrayOrTraversable($stepSizes);
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
   * @param \PapayaUiReference $reference
   * @return null|\PapayaUiReference
   */
  public function reference(\PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new \PapayaUiReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}

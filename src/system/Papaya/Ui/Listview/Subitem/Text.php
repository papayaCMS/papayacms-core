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

namespace Papaya\Ui\Listview\Subitem;
/**
 * A simple listview subitem displaying text.
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property integer $align
 * @property string|\PapayaUiString $text
 * @property array $actionParameters
 * @property \PapayaUiReference $reference
 */
class Text extends \Papaya\Ui\Listview\Subitem {

  /**
   * buffer for text variable
   *
   * @var string|\PapayaUiString
   */
  protected $_text = '';

  /**
   * Basic reference/link
   *
   * @var \PapayaUiReference
   */
  protected $_reference = NULL;

  /**
   * @var null
   */
  protected $_actionParameters = NULL;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'text' => array('_text', '_text'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
    'reference' => array('reference', 'reference')
  );

  /**
   * Create subitem object, set text content and alignment.
   *
   * @param string|\PapayaUiString $text
   * @param array $actionParameters
   */
  public function __construct($text, array $actionParameters = NULL) {
    $this->_text = $text;
    $this->setActionParameters($actionParameters);
  }

  /**
   * Getter/Setter for the reference subobject, this will be initalized from the listview
   * if not set.
   *
   * @param \PapayaUiReference $reference
   * @return \PapayaUiReference
   */
  public function reference(\PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (is_null($this->_reference)) {
      // directly return the reference, so it is possible to recognice if it was set.
      /** @noinspection PhpUndefinedMethodInspection */
      return $this->collection()->getListview()->reference();
    }
    return $this->_reference;
  }

  /**
   * Append subitem xml data to parent node.
   *
   * @param \Papaya\Xml\Element $parent
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $subitem = $parent->appendElement(
      'subitem',
      array(
        'align' => \Papaya\Ui\Option\Align::getString($this->getAlign())
      )
    );
    if (!empty($this->_actionParameters)) {
      $subitem->appendElement('a', array('href' => $this->getUrl()), (string)$this->_text);
    } else {
      $subitem->appendText((string)$this->_text);
    }
  }

  /**
   * Use the action parameter and the reference from the items to get an url for the output xml.
   *
   * If you assigned a reference object the action parameters will be applied without an additional
   * parameter group. If the reference is fetched from the listview, the listview parameter group
   * will be used.
   *
   * @return string
   */
  protected function getUrl() {
    $reference = clone $this->reference();
    if (isset($this->_reference)) {
      $reference->setParameters($this->_actionParameters);
    } else {
      /** @noinspection PhpUndefinedMethodInspection */
      $reference->setParameters(
        $this->_actionParameters,
        $this->collection()->getListview()->parameterGroup()
      );
    }
    return $reference->getRelative();
  }
}

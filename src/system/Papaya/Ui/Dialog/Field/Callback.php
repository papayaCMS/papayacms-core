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
* A dialog field with a control that is generated by a callback
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldCallback extends PapayaUiDialogField {

  /**
  * Field xml generation callback
  * @var integer
  */
  private $_callback = 'null';

  /**
   *
   * @var boolean
   */
  protected $_isXhtml = FALSE;

  /**
  * Initialize object, set caption, field name and maximum length
  *
  * @param string|\PapayaUiString $caption
  * @param string $name
  * @param callback $callback
  * @param mixed $default
  * @param \PapayaFilter|NULL $filter
  */
  public function __construct(
    $caption, $name, $callback, $default = NULL, \PapayaFilter $filter = NULL
  ) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->_callback = $callback;
    $this->setDefaultValue($default);
    if (isset($filter)) {
      $this->setFilter($filter);
    }
  }

  /**
  * Append field and input ouptut to DOM
  *
  * @param \PapayaXmlElement $parent
  * @return \PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    if (is_callable($this->_callback)) {
      $target = $this->_isXhtml ? $field->appendElement('xhtml') : $field;
      $content = call_user_func(
        $this->_callback, $this->getName(), $this, $this->getCurrentValue()
      );
      if ($content instanceof \PapayaXmlAppendable) {
        $target->append($content);
      } elseif ($content instanceof \DOMElement) {
        $target->appendChild($field->ownerDocument->importNode($content, TRUE));
      } elseif (is_string($content) || $content instanceof \PapayaUiString) {
        $target->appendXml((string)$content);
      }
    }
    return $field;
  }
}

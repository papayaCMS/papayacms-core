<?php
/**
* A menu/toolbar button list to select a single value out of a list.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Buttons.php 38906 2013-11-04 14:59:11Z weinert $
*/

/**
* A menu/toolbar button list to select a single value out of a list.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiReference $reference
* @property string $parameterName
* @property string|PapayaUiString $caption
* @property Traversable|array $options
* @property string|PapayaUiString $defaultOption
* @property string|integer|boolean $currentValue
*/
class PapayaUiToolbarSelectButtons extends PapayaUiToolbarSelect {

  /**
   * Append button xml elemens to parent element.
   *
   * @param PapayaXmlElement $parent
   * @return PapayaXmlElement
   */
  public function appendTo(PapayaXmlElement $parent) {
    $currentValue = $this->getCurrentValue();
    $parameterName = new PapayaRequestParametersName($this->_parameterName);
    foreach ($this->_options as $value => $data) {
      if (is_array($data)) {
        $caption = PapayaUtilArray::get($data, array('caption', 0), '');
        $image = PapayaUtilArray::get($data, array('image', 1), '');
      } else {
        $caption = $data;
        $image = '';
      }
      $reference = clone $this->reference();
      $reference->getParameters()->set((string)$parameterName, $value);
      $button = $parent->appendElement(
        'button',
        array(
          'href' => $reference->getRelative(),
          'title' => (string)$caption,
          'image' => empty($image) ? '' : (string)$this->papaya()->images[$image]
        )
      );
      if ($currentValue == $value) {
        $button->setAttribute('down', 'down');
      };
    }
    return $parent;
  }
}
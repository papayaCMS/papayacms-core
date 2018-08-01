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
* A menu/toolbar button list to select a single value out of a list.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property \PapayaUiReference $reference
* @property string $parameterName
* @property string|\PapayaUiString $caption
* @property Traversable|array $options
* @property string|\PapayaUiString $defaultOption
* @property string|integer|boolean $currentValue
*/
class PapayaUiToolbarSelectButtons extends \PapayaUiToolbarSelect {

  /**
   * Append button xml elemens to parent element.
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $currentValue = $this->getCurrentValue();
    $parameterName = new \Papaya\Request\Parameters\Name($this->_parameterName);
    foreach ($this->_options as $value => $data) {
      if (is_array($data)) {
        if (array_key_exists('enabled', $data) && !$data['enabled']) {
          continue;
        }
        $caption = \Papaya\Utility\Arrays::get($data, array('caption', 0), '');
        $image = \Papaya\Utility\Arrays::get($data, array('image', 1), '');
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

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
namespace Papaya\UI\Toolbar\Select;

/**
 * A menu/toolbar button list to select a single value out of a list.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\Reference $reference
 * @property string $parameterName
 * @property string|\Papaya\UI\Text $caption
 * @property \Traversable|array $options
 * @property string|\Papaya\UI\Text $defaultOption
 * @property string|int|bool $currentValue
 */
class Buttons extends \Papaya\UI\Toolbar\Select {
  /**
   * Append button xml elemens to parent element.
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $currentValue = $this->getCurrentValue();
    $parameterName = new \Papaya\Request\Parameters\Name($this->_parameterName);
    foreach ($this->_options as $value => $data) {
      if (\is_array($data)) {
        if (\array_key_exists('enabled', $data) && !$data['enabled']) {
          continue;
        }
        $caption = \Papaya\Utility\Arrays::get($data, ['caption', 0], '');
        $image = \Papaya\Utility\Arrays::get($data, ['image', 1], '');
      } else {
        $caption = $data;
        $image = '';
      }
      $reference = clone $this->reference();
      $reference->getParameters()->set((string)$parameterName, $value);
      $button = $parent->appendElement(
        'button',
        [
          'href' => $reference->getRelative(),
          'title' => (string)$caption,
          'image' => empty($image) ? '' : (string)$this->papaya()->images[$image]
        ]
      );
      if ($currentValue == $value) {
        $button->setAttribute('down', 'down');
      }
    }
    return $parent;
  }
}

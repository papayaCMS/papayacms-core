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
namespace Papaya\UI\Toolbar\Select {

  use Papaya\BaseObject\Interfaces\StringCastable;
  use Papaya\Request;
  use Papaya\UI;
  use Papaya\Utility;
  use Papaya\XML\Element as XMLElement;

  /**
   * A menu/toolbar button list to select a single value out of a list.
   *
   * @package Papaya-Library
   * @subpackage UI
   *
   * @property UI\Reference $reference
   * @property string $parameterName
   * @property string|StringCastable $caption
   * @property \Traversable|array $options
   * @property string|StringCastable $defaultOption
   * @property string|int|bool $currentValue
   */
  class Buttons extends UI\Toolbar\Select {
    /**
     * Append button xml elements to parent element.
     *
     * @param XMLElement $parent
     * @return XMLElement
     */
    public function appendTo(XMLElement $parent) {
      $currentValue = (string)$this->getCurrentValue();
      $parameterName = new Request\Parameters\Name($this->_parameterName);
      foreach ($this->_options as $value => $data) {
        if (\is_array($data)) {
          if (\array_key_exists('enabled', $data) && !$data['enabled']) {
            continue;
          }
          $caption = Utility\Arrays::get($data, ['caption', 0], '');
          $image = Utility\Arrays::get($data, ['image', 1], '');
          $hint = Utility\Arrays::get($data, ['hint', 2], '');
        } else {
          $caption = $data;
          $image = '';
          $hint = '';
        }
        $reference = clone $this->reference();
        $reference->getParameters()->set((string)$parameterName, $value);
        $button = $parent->appendElement(
          'button',
          [
            'href' => $reference->getRelative(),
            'title' => (string)$caption,
            'hint' => (string)$hint,
            'image' => empty($image) ? '' : (string)($this->papaya()->images[$image] ?? $image)
          ]
        );
        if (
          (is_int($value) && (int)$currentValue === $value) ||
          $currentValue === (string)$value
        ) {
          $button->setAttribute('down', 'down');
        }
      }
      return $parent;
    }
  }
}

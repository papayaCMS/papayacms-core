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

namespace Papaya\UI\Dialog\Field\Select;
/**
 * A selection field displaing the available languages
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Language extends \Papaya\UI\Dialog\Field\Select {

  const OPTION_ALLOW_ANY = 1;
  const OPTION_USE_IDENTIFIER = 2;

  public function __construct(
    $caption, $name, \Papaya\Content\Languages $languages = NULL, $options = 0
  ) {
    // @codeCoverageIgnoreStart
    if (NULL === $languages) {
      $languages = $this->papaya()->languages;
    }
    // @codeCoverageIgnoreEnd
    $items = array();
    if (\Papaya\Utility\Bitwise::inBitmask(self::OPTION_USE_IDENTIFIER, $options)) {
      foreach ($languages as $language) {
        $items[$language['identifier']] = $language;
      }
      $any = '*';
    } else {
      $items = $languages;
      $any = 0;
    }
    if (\Papaya\Utility\Bitwise::inBitmask(self::OPTION_ALLOW_ANY, $options)) {
      $values = new \Papaya\Iterator\Union(
        \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
        array($any => new \Papaya\UI\Text\Translated('Any')),
        $items
      );
    } else {
      $values = $items;
    }
    parent::__construct($caption, $name, $values);
  }

  public function appendTo(\Papaya\XML\Element $parent) {
    $this->callbacks()->getOptionCaption = array($this, 'callbackGetLanguageCaption');
    return parent::appendTo($parent);
  }

  public function callbackGetLanguageCaption($context, $language) {
    if (is_array($language)) {
      return $language['title'].' ('.$language['code'].')';
    } else {
      return (string)$language;
    }
  }
}

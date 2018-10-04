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

use Papaya\Content;
use Papaya\Iterator;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * A selection field displaying the available languages
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Language extends UI\Dialog\Field\Select {
  const OPTION_ALLOW_ANY = 1;

  const OPTION_USE_IDENTIFIER = 2;

  /**
   * Language constructor.
   *
   * @param $caption
   * @param $name
   * @param Content\Languages|null $languages
   * @param int $options
   */
  public function __construct(
    $caption, $name, Content\Languages $languages = NULL, $options = 0
  ) {
    // @codeCoverageIgnoreStart
    if (NULL === $languages) {
      /** @noinspection CallableParameterUseCaseInTypeContextInspection */
      $languages = $this->papaya()->languages;
    }
    // @codeCoverageIgnoreEnd
    $items = [];
    if (Utility\Bitwise::inBitmask(self::OPTION_USE_IDENTIFIER, $options)) {
      foreach ($languages as $language) {
        $items[$language['identifier']] = $language;
      }
      $any = '*';
    } else {
      $items = $languages;
      $any = 0;
    }
    if (Utility\Bitwise::inBitmask(self::OPTION_ALLOW_ANY, $options)) {
      $values = new Iterator\Union(
        Iterator\Union::MIT_KEYS_ASSOC,
        [$any => new UI\Text\Translated('Any')],
        $items
      );
    } else {
      $values = $items;
    }
    parent::__construct($caption, $name, $values);
  }

  /**
   * @param XML\Element $parent
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $this->callbacks()->getOptionCaption = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $language
    ) {
      if (\is_array($language)) {
        return $language['title'].' ('.$language['code'].')';
      }
      return (string)$language;
    };
    return parent::appendTo($parent);
  }
}

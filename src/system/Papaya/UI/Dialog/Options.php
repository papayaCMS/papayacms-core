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
namespace Papaya\UI\Dialog;

use Papaya\BaseObject\Options\Defined as DefinedOptions;
use Papaya\XML;

/**
 * /**
 * The options for a dialog object are encapsulated into a separate class, this allows
 * different implementations to use them and cleans up the dialog class interface a little.
 *
 * Not any dialog implementation has to use all options.
 *
 * @property bool $useConfirmation a hidden field used to validate that the form was submitted
 * @property bool $useToken use a token to protect the form against csrf
 * @property bool $protectChanges activate javascript change protection
 * @property int $captionStyle visibility/position of the field captions
 * @property bool $topButtons show buttons at dialog top
 * @property bool $bottomButtons show buttons at dialog bottom
 * @property string $dialogWidth larger dialogs have more space for captions
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Options
  extends DefinedOptions {
  /**
   * Show no field captions
   *
   * @var int
   */
  const CAPTION_NONE = 0;

  /**
   * Show field captions at the side of the fields
   *
   * @var int
   */
  const CAPTION_SIDE = 1;

  /**
   * Show field captions on top of the fields
   *
   * @var int
   */
  const CAPTION_TOP = 2;

  /**
   * @var string
   */
  const SIZE_XS = 's';

  /**
   * @var string
   */
  const SIZE_S = 's';

  /**
   * @var string
   */
  const SIZE_M = 'm';

  /**
   * @var string
   */
  const SIZE_L = 'l';

  /**
   * @var string
   */
  const SIZE_SMALL = self::SIZE_S;

  /**
   * @var string
   */
  const SIZE_MEDIUM = self::SIZE_M;

  /**
   * @var string
   */
  const SIZE_LARGE = self::SIZE_L;

  /**
   * Dialog option definitions: The key is the option name, the element a list of possible values.
   *
   * USE_TOKEN: use a token to protect the form against csrf
   * PROTECT_CHANGES: activate javascript change protection
   * CAPTION_STYLE: visibility/position of the field captions
   * TOP_BUTTONS : show buttons at dialog top
   * BOTTOM_BUTTONS : show buttons at dialog bottom
   *
   * @var array
   */
  private static $_definitions = [
    'USE_CONFIRMATION' => [TRUE, FALSE],
    'USE_TOKEN' => [TRUE, FALSE],
    'PROTECT_CHANGES' => [TRUE, FALSE],
    'CAPTION_STYLE' => [self::CAPTION_NONE, self::CAPTION_SIDE, self::CAPTION_TOP],
    'DIALOG_WIDTH' => [self::SIZE_M, self::SIZE_S, self::SIZE_XS, self::SIZE_L],
    'TOP_BUTTONS' => [TRUE, FALSE],
    'BOTTOM_BUTTONS' => [TRUE, FALSE]
  ];

  /**
   * Dialog option values
   *
   * @var array
   */
  protected $_options = [
    'CAPTION_STYLE' => self::CAPTION_SIDE,
    'DIALOG_WIDTH' => self::SIZE_MEDIUM,
    'TOP_BUTTONS' => FALSE,
  ];

  /**
   * Options constructor.
   *
   * @param array|null $options
   */
  public function __construct(array $options = NULL) {
    parent::__construct(self::$_definitions, $options);
  }

  /**
   * Append options to an xml element
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $options = $parent->appendElement('options');
    foreach ($this as $name => $value) {
      $options->appendElement(
        'option',
        ['name' => $name, 'value' => $this->_valueToString($value)]
      );
    }
  }

  /**
   * Convert the value into a more readable string representation
   *
   * @param mixed $value
   *
   * @return string
   */
  private function _valueToString($value) {
    if (\is_bool($value)) {
      return $value ? 'yes' : 'no';
    }
    return (string)$value;
  }
}

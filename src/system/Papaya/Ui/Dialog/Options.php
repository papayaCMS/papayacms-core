<?php
/**
* The options for a dialog object are encapsulated into a separate class, this allows
* different implementations to use them and cleans up the dialog class interface a little.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Options.php 37654 2012-11-08 16:20:49Z weinert $
*/

/**
/**
* The options for a dialog object are encapsulated into a separate class, this allows
* different implementations to use them and cleans up the dialog class interface a little.
*
* Not any dialog implementation has to use all options.
*
* @property boolean $useConfirmation a hidden field used to validate that the form was submitted
* @property boolean $useToken use a token to protect the form against csrf
* @property boolean $protectChanges activate javascript change protection
* @property integer $captionStyle visibility/position of the field captions
* @property boolean $topButtons show buttons at dialog top
* @property boolean $bottomButtons show buttons at dialog bottom
* @property string $dialogWidth larger dialogs have more space for captions
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogOptions
   extends PapayaObjectOptionsDefined {

  /**
  * Show no field captions
  * @var integer
  */
  const CAPTION_NONE = 0;

  /**
  * Show field captions at the side of the fields
  * @var integer
  */
  const CAPTION_SIDE = 1;

  /**
  * Show field captions on top of the fields
  * @var integer
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
  protected $_definitions = array(
    'USE_CONFIRMATION' => array(TRUE, FALSE),
    'USE_TOKEN' => array(TRUE, FALSE),
    'PROTECT_CHANGES' => array(TRUE, FALSE),
    'CAPTION_STYLE' => array(self::CAPTION_NONE, self::CAPTION_SIDE, self::CAPTION_TOP),
    'DIALOG_WIDTH' => array(self::SIZE_M, self::SIZE_S, self::SIZE_XS,self::SIZE_L),
    'TOP_BUTTONS' => array(TRUE, FALSE),
    'BOTTOM_BUTTONS' => array(TRUE, FALSE)
  );

  /**
  * Dialog option values
  * @var array
  */
  protected $_options = array(
    'CAPTION_STYLE' => self::CAPTION_SIDE,
    'DIALOG_WIDTH' => self::SIZE_MEDIUM,
    'TOP_BUTTONS' => FALSE,
  );

  /**
  * Append options to an xml element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $options = $parent->appendElement('options');
    foreach ($this as $name => $value) {
      $options->appendElement(
        'option',
        array('name' => $name, 'value' => $this->_valueToString($value))
      );
    }
  }

  /**
  * Convert the value into a more readable string representation
  *
  * @param mixed $value
  * @return string
   */
  private function _valueToString($value) {
    if (is_bool($value)) {
      return ($value) ? 'yes' : 'no';
    } else {
      return (string)$value;
    }
  }
}
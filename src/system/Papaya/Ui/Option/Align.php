<?php
/**
* Generic definition and handling for alignment attribute values.
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
* @version $Id: Align.php 35572 2011-03-29 10:39:29Z weinert $
*/

/**
* Generic definition and handling for alignment attribute values.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiOptionAlign {

  /**
  * Alignment left
  *
  * @var integer
  */
  const LEFT = 0;
  /**
  * Alignment center
  *
  * @var integer
  */
  const CENTER = 1;
  /**
  * Alignment right
  *
  * @var integer
  */
  const RIGHT = 2;

  /**
  * string representation of the column alignment, used in the xml attributes
  *
  * @var array
  */
  private static $_alignAttributes = array(
    self::LEFT => 'left',
    self::CENTER => 'center',
    self::RIGHT => 'right'
  );

  /**
  * Get the string representation of the alignment value for a xml attribute. If the align
  * argument is invalid it will return "left".
  *
  * @param integer $align
  * @return string
  */
  public static function getString($align) {
    if (isset(self::$_alignAttributes[$align])) {
      return self::$_alignAttributes[$align];
    } else {
      return self::$_alignAttributes[self::LEFT];
    }
  }

  /**
  * Validate an alignment value. This will throw an exception if the argument is invalid.
  * An individual message for the exception can be provided, too.
  *
  * @throws InvalidArgumentException
  * @param integer $align
  * @param string $message
  * @return TRUE
  */
  public static function validate($align, $message = NULL) {
    if (isset(self::$_alignAttributes[$align])) {
      return TRUE;
    } elseif (isset($message)) {
      throw new InvalidArgumentException($message);
    } else {
      throw new InvalidArgumentException(
        sprintf('InvalidArgumentException: Invalid align value "%d".', $align)
      );
    }
  }
}
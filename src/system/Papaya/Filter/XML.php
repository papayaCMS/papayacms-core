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
namespace Papaya\Filter;

use Papaya\Filter;

/**
 * Papaya filter class for xml strings.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class XML implements Filter {
  /**
   * @var bool
   */
  private $_allowFragments;

  /**
   * @param bool $allowFragments
   */
  public function __construct($allowFragments = TRUE) {
    $this->_allowFragments = $allowFragments;
  }

  /**
   * Check the value if it's a xml string, if not throw an exception.
   *
   *
   * @param mixed $value
   *
   * @throws Exception
   *
   * @return true
   */
  public function validate($value) {
    $value = \trim($value);
    if (empty($value)) {
      throw new Exception\IsEmpty();
    }
    $errors = new \Papaya\XML\Errors();
    $errors->activate();
    $document = new \Papaya\XML\Document();
    try {
      if ($this->_allowFragments) {
        $root = $document->appendElement('root');
        $root->appendXML($value);
      } else {
        $document->loadXML($value);
      }
      $errors->emit(TRUE);
    } catch (\Papaya\XML\Exception $e) {
      throw new Exception\InvalidXML($e);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read an input value if it is valid.
   *
   * @param mixed $value
   *
   * @return string
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return (string)$value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}

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

/**
 * Papaya filter class for xml strings.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class XML implements \Papaya\Filter {
  /**
   * @var bool
   */
  private $_allowFragments = TRUE;

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
   * @param string $value
   * @throws \Papaya\Filter\Exception\InvalidXML
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @return true
   */
  public function validate($value) {
    $value = \trim($value);
    if (empty($value)) {
      throw new \Papaya\Filter\Exception\IsEmpty();
    }
    $errors = new \Papaya\XML\Errors();
    $errors->activate();
    $dom = new \Papaya\XML\Document();
    try {
      if ($this->_allowFragments) {
        $root = $dom->appendElement('root');
        $root->appendXML($value);
      } else {
        $dom->loadXML($value);
      }
      $errors->emit(TRUE);
    } catch (\Papaya\XML\Exception $e) {
      throw new \Papaya\Filter\Exception\InvalidXML($e);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read an input value if it is valid.
   *
   * @param string $value
   * @return string
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (\Papaya\Filter\Exception $e) {
      return;
    }
  }
}

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
namespace Papaya\Filter\URL;

use Papaya\Filter;

/**
 * Papaya filter class validating a url host name
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Web extends Filter\URL {
  /**
   * @param mixed $value
   * @return true
   * @throws \Papaya\Filter\Exception
   */
  public function validate($value) {
    return parent::validate($this->prepare($value));
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value) {
    return parent::filter($this->prepare($value));
  }

  /**
   * prefix the value if needed with http://
   *
   * @param mixed $value
   *
   * @return string
   */
  private function prepare($value) {
    if (!empty($value) && \is_string($value) && !\preg_match('(^https?://)', $value)) {
      return 'http://'.$value;
    }
    return $value;
  }
}

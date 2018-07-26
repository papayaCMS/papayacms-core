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

class PapayaFilterLines implements Papaya\Filter {

  /**
   * @var \Papaya\Filter
   */
  private $_filter;

  public function __construct(Papaya\Filter $filter) {
    $this->_filter = $filter;
  }

  public function filter($value) {
    $lines = [];
    foreach ($this->getLines((string)$value) as $line) {
      $line = $this->_filter->filter($line);
      if ($line !== NULL && $line !== '') {
        $lines[] = $line;
      }
    }
    return implode("\n", $lines);
  }

  public function validate($value) {
    foreach ($this->getLines((string)$value) as $line) {
      $this->_filter->validate($line);
    }
  }

  private function getLines($string) {
    if (preg_match_all('(^.+$)m', $string, $matches, PREG_PATTERN_ORDER)) {
      return $matches[0];
    }
    return [];
  }
}

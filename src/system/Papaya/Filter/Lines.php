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

class Lines implements Filter {
  /**
   * @var Filter
   */
  private $_filter;

  /**
   * Lines constructor.
   *
   * @param Filter $filter
   */
  public function __construct(Filter $filter) {
    $this->_filter = $filter;
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value) {
    $lines = [];
    foreach ($this->getLines((string)$value) as $line) {
      $filteredLine = $this->_filter->filter($line);
      if (NULL !== $filteredLine && '' !== $filteredLine) {
        $lines[] = $filteredLine;
      }
    }
    return \implode("\n", $lines);
  }

  /**
   * @param mixed $value
   * @return true
   * @throws \Papaya\Filter\Exception
   */
  public function validate($value) {
    foreach ($this->getLines((string)$value) as $line) {
      $this->_filter->validate($line);
    }
    return true;
  }

  /**
   * @param string $string
   * @return array
   */
  private function getLines($string) {
    if (\preg_match_all('(^([^\\r\\n]+))m', $string, $matches, PREG_PATTERN_ORDER)) {
      return $matches[1];
    }
    return [];
  }
}

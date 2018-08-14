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

namespace Papaya\Database\Sequence;
/**
 * Generator that uses a restricted set of symbols which can be conveniently used by humans.
 *
 * Usage:
 *   $sequence = new \Papaya\Database\Sequence\Base32(
 *     'tablename', 'fieldname', 10
 *   );
 *   $newId = $sequence->next();
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Human extends \Papaya\Database\Sequence {

  /**
   * List of character used in the id
   */
  private $_characterTable = array(
    'a',
    'b',
    'c',
    'd',
    'e',
    'f',
    'g',
    'h',
    'i',
    'j',
    'k',
    'l',
    'm',
    'n',
    'o',
    'p',
    'q',
    'r',
    's',
    't',
    'u',
    'v',
    'w',
    'x',
    'y',
    'z',
    '2',
    '3',
    '4',
    '5',
    '6',
    '7'
  );

  /**
   * identifier length
   *
   * @var integer
   */
  private $_length = 10;

  /**
   * Initialize object and default properties, optionally set count
   *
   * @param string $table
   * @param string $field
   * @param integer $length
   */
  public function __construct($table, $field, $length = 10) {
    parent::__construct($table, $field);
    $this->_length = $length;
  }

  /**
   * create an random identifier string
   *
   * @return string
   */
  public function create() {
    return $this->getRandomCharacters($this->_length);
  }

  /**
   * Get $_length random symbols from $_characterTable
   *
   * @param int $length
   * @return string
   */
  protected function getRandomCharacters($length) {
    $result = '';
    $max = count($this->_characterTable) - 1;
    for ($i = 0; $i < $length; $i++) {
      $result .= $this->_characterTable[\Papaya\Utility\Random::rand(0, $max)];
    }
    return $result;
  }
}

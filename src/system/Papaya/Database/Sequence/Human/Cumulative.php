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

/**
* Class generating human readable sequence strings with ascending length. The
* module produces some ids that will be tried against the database and have
* increasing length, so if the database is not very populated yet, there is
* a good chance that ids are short. There are $minimumLength and $maximumLength
* parameters. There are $count ids generated with increasing length,
* remaining ids to generate will also have $maximumLength as length.
*
* Ids are by design not enumerable, that is: random, unpredictable.
*
* Ids are NOT prefix-free codes.
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseSequenceHumanCumulative extends \PapayaDatabaseSequenceHuman {

  /**
  * @var integer minimum length.
  */
  private $_minimumLength = 2;

  /**
  * @var integer maximum length.
  */
  private $_maximumLength = 32;

  /**
  * @var float current length for iteration.
  */
  private $_cumulativeLength = 32;

  /**
  * @var float the step size of each increase.
  */
  private $_cumulativeStep = 1;

  public function __construct($table, $field, $minimumLength = 2, $maximumLength = 32) {
    parent::__construct($table, $field);
    if ($minimumLength <= $maximumLength) {
      $this->_minimumLength = $minimumLength;
      $this->_maximumLength = $maximumLength;
      $this->_cumulativeLength = $this->_maximumLength;
    } else {
      throw new \InvalidArgumentException(
        'Minimum length can not be greater then maximum length.'
      );
    }
  }

  /**
  * Create a random Id of a length of at least $this->_minimumLength and at most
  * $this->_maximumLength.
  *
  * @return string
  */
  public function create() {
    $result = $this->getRandomCharacters(round($this->_cumulativeLength));
    if ($this->_cumulativeLength < $this->_maximumLength) {
      $this->_cumulativeLength += $this->_cumulativeStep;
    }
    return $result;
  }

  /**
  * Create several ids with increasing length. It start at minimum lenght and increases
  * the length so that tha last element always is one of maximum length.
  *
  * @param integer $count
  * @return array
  */
  protected function createIdentifiers($count) {
    if ($count > 1) {
      $this->_cumulativeLength = $this->_minimumLength;
      $this->_cumulativeStep = ($this->_maximumLength - $this->_minimumLength) / ($count - 1);
    } else {
      $this->_cumulativeLength = $this->_maximumLength;
      $this->_cumulativeStep = 1;
    }
    return parent::createIdentifiers($count);
  }
}

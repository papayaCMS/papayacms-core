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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorRegexReplaceTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\Regex\Replace
  */
  public function testIteration() {
    $iterator = new \Papaya\Iterator\Regex\Replace(
      new ArrayIterator(array('21 42', '42 84')),
      '(\d+)',
      '#$0'
    );
    $this->assertEquals(
      array(
        0 => '#21 #42',
        1 => '#42 #84'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Regex\Replace
  */
  public function testIterationLimitReplace() {
    $iterator = new \Papaya\Iterator\Regex\Replace(
      new ArrayIterator(array('21 42', '42 84')),
      '(\d+)',
      '#$0',
      1
    );
    $this->assertEquals(
      array(
        0 => '#21 42',
        1 => '#42 84'
      ),
      iterator_to_array($iterator)
    );
  }
}

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

namespace Papaya\Configuration;

require_once __DIR__.'/../../../bootstrap.php';

class IteratorTest extends \PapayaTestCase {

  public function testIterator() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Configuration $configuration */
    $configuration = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $configuration
      ->expects($this->any())
      ->method('get')
      ->will($this->returnValue(42));
    $iterator = new Iterator(array('SAMPLE_INT'), $configuration);
    $result = iterator_to_array($iterator);
    $this->assertEquals(
      array('SAMPLE_INT' => 42),
      $result
    );
  }

}

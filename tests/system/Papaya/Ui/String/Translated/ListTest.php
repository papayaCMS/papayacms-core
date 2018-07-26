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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiStringTranslatedListTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiStringTranslatedList::__construct
   */
  public function testConstructorWithArray() {
    $list = new \PapayaUiStringTranslatedList(array('foo'));
    $this->assertInstanceOf(PapayaIteratorTraversable::class, $list->getInnerIterator());
  }

  /**
   * @covers \PapayaUiStringTranslatedList
   */
  public function testIterationCallsTranslation() {
    $phrases = $this
      ->getMockBuilder(PapayaPhrases::class)
      ->disableOriginalConstructor()
      ->getMock();
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('foo')
      ->will($this->returnValue('bar'));
    $list = new \PapayaUiStringTranslatedList(array('foo'));
    $list->papaya(
      $this->mockPapaya()->application(array('Phrases' => $phrases))
    );
    $this->assertEquals(
      array('bar'),
      iterator_to_array($list)
    );
  }

  /**
  * @covers \PapayaUiStringTranslatedList::papaya
  */
  public function testPapayaGetUsingSingleton() {
    $list = new \PapayaUiStringTranslatedList(array());
    $this->assertInstanceOf(
      PapayaApplication::class, $list->papaya()
    );
  }

  /**
  * @covers \PapayaUiStringTranslatedList::papaya
  */
  public function testPapayaGetAfterSet() {
    $list = new \PapayaUiStringTranslatedList(array());
    $application = $this->createMock(PapayaApplication::class);
    $this->assertSame($application, $list->papaya($application));
  }
}


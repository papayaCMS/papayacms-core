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

namespace Papaya\UI\Text\Translated;
require_once __DIR__.'/../../../../../bootstrap.php';

class CollectionTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Text\Translated\Collection::__construct
   */
  public function testConstructorWithArray() {
    $list = new Collection(array('foo'));
    $this->assertInstanceOf(\Papaya\Iterator\TraversableIterator::class, $list->getInnerIterator());
  }

  /**
   * @covers \Papaya\UI\Text\Translated\Collection
   */
  public function testIterationCallsTranslation() {
    $phrases = $this
      ->getMockBuilder(\Papaya\Phrases::class)
      ->disableOriginalConstructor()
      ->getMock();
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('foo')
      ->will($this->returnValue('bar'));
    $list = new Collection(array('foo'));
    $list->papaya(
      $this->mockPapaya()->application(array('Phrases' => $phrases))
    );
    $this->assertEquals(
      array('bar'),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\UI\Text\Translated\Collection::papaya
   */
  public function testPapayaGetUsingSingleton() {
    $list = new Collection(array());
    $this->assertInstanceOf(
      \Papaya\Application::class, $list->papaya()
    );
  }

  /**
   * @covers \Papaya\UI\Text\Translated\Collection::papaya
   */
  public function testPapayaGetAfterSet() {
    $list = new Collection(array());
    $application = $this->createMock(\Papaya\Application::class);
    $this->assertSame($application, $list->papaya($application));
  }
}


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

namespace Papaya\Administration\Pages\Dependency {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class PapayaAdministrationPagesDependencyCounterTest extends \Papaya\TestCase {

    /**
     * @covers Counter::__construct
     */
    public function testConstructor() {
      $counter = new Counter(42);
      $this->assertAttributeEquals(42, '_pageId', $counter);
    }

    /**
     * @covers Counter::load
     */
    public function testLoad() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->any())
        ->method('fetchRow')
        ->with(\Papaya\Database\Result::FETCH_ASSOC)
        ->will(
          $this->onConsecutiveCalls(
            array(
              'name' => 'dependencies',
              'counter' => 21
            ),
            array(
              'name' => 'references',
              'counter' => 23
            )
          )
        );

      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->isType('string'),
          array(
            'table_'.\Papaya\Content\Tables::PAGE_DEPENDENCIES,
            'table_'.\Papaya\Content\Tables::PAGE_REFERENCES,
            42
          )
        )
        ->will(
          $this->returnValue($databaseResult)
        );

      $counter = new Counter(42);
      $counter->setDatabaseAccess($databaseAccess);
      $this->assertTrue($counter->load());
      $this->assertEquals(21, $counter->getDependencies());
      $this->assertEquals(23, $counter->getReferences());
    }

    /**
     * @covers Counter::load
     */
    public function testLoadFailedExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->withAnyParameters()
        ->will($this->returnValue(FALSE));

      $counter = new Counter(42);
      $counter->setDatabaseAccess($databaseAccess);
      $this->assertFalse($counter->load());
    }

    /**
     * @covers Counter::getDependencies
     * @covers Counter::lazyLoad
     */
    public function testGetDependencies() {
      $counter = new \Papaya\Administration\Pages\Dependency\Counter_TestProxy(42);
      $this->assertEquals(
        21, $counter->getDependencies()
      );
    }

    /**
     * @covers Counter::getReferences
     * @covers Counter::lazyLoad
     */
    public function testGetReferences() {
      $counter = new \Papaya\Administration\Pages\Dependency\Counter_TestProxy(42);
      $this->assertEquals(
        23, $counter->getReferences()
      );
    }

    /**
     * @covers       Counter::getLabel
     * @covers       Counter::lazyLoad
     * @dataProvider provideCountingsForGetLabel
     * @param string $expected
     * @param int $dependencies
     * @param int $references
     */
    public function testGetLabel($expected, $dependencies, $references) {
      $counter = new \Papaya\Administration\Pages\Dependency\Counter_TestProxy(42);
      $counter->countingSamples = array(
        'dependencies' => $dependencies,
        'references' => $references
      );
      $this->assertEquals(
        $expected, $counter->getLabel()
      );
    }

    public function testGetLabelWithAllParameters() {
      $counter = new \Papaya\Administration\Pages\Dependency\Counter_TestProxy(42);
      $counter->countingSamples = array(
        'dependencies' => 21,
        'references' => 23
      );
      $this->assertEquals(
        '_{21:23}', $counter->getLabel(':', '_{', '}')
      );
    }

    /**************************
     * Data Provider
     **************************/

    public static function provideCountingsForGetLabel() {
      return array(
        'no dependencies, no references' => array('', 0, 0),
        'no dependencies, 5 references' => array(' (5)', 0, 5),
        '2 dependencies, no references' => array(' (2/0)', 2, 0),
        '3 dependencies, 7 references' => array(' (3/7)', 3, 7)
      );
    }
  }

  class Counter_TestProxy
    extends Counter {

    public $countingSamples = array(
      'dependencies' => 21,
      'references' => 23
    );

    public function load() {
      if (is_array($this->countingSamples)) {
        $this->_amounts = $this->countingSamples;
        return TRUE;
      }
      return FALSE;
    }
  }
}

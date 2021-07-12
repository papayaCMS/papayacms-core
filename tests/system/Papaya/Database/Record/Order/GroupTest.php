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

namespace Papaya\Database\Record\Order;

require_once __DIR__.'/../../../../../bootstrap.php';

class GroupTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Database\Record\Order\Group
   */
  public function testConstructorWithTwoLists() {
    $group = new Group(
      $this->getListFixture(array('one', 'two')),
      $this->getListFixture(array('three', 'four'))
    );
    $this->assertEquals('one, two, three, four', (string)$group);
  }

  /**
   * @covers \Papaya\Database\Record\Order\Group
   */
  public function testAdd() {
    $group = new Group();
    $group->add(
      $this->getListFixture(array('one', 'two'))
    );
    $this->assertEquals('one, two', (string)$group);
  }

  /**
   * @covers \Papaya\Database\Record\Order\Group
   */
  public function testAddMovesExistingToEnd() {
    $group = new Group(
      $list = $this->getListFixture(array('one', 'two')),
      $this->getListFixture(array('three', 'four'))
    );
    $group->add($list);
    $this->assertEquals('three, four, one, two', (string)$group);
  }

  /**
   * @covers \Papaya\Database\Record\Order\Group
   */
  public function testRemove() {
    $group = new Group(
      $this->getListFixture(array('one', 'two')),
      $list = $this->getListFixture(array('three', 'four'))
    );
    $group->remove($list);
    $this->assertEquals('one, two', (string)$group);
  }

  /*********************
   * Fixtures
   ********************/

  /**
   * @param array $fieldNames
   * @return \PHPUnit_Framework_MockObject_MockObject|By\Fields
   */
  private function getListFixture(array $fieldNames = array()) {
    $fields = array();
    foreach ($fieldNames as $name) {
      $fields[] = $field = $this
        ->getMockBuilder(Field::class)
        ->disableOriginalConstructor()
        ->getMock();
      $field
        ->expects($this->any())
        ->method('__toString')
        ->will($this->returnValue($name));
    }
    $result = $this
      ->getMockBuilder(By\Fields::class)
      ->disableOriginalConstructor()
      ->getMock();
    $result
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator($fields)));
    return $result;
  }
}

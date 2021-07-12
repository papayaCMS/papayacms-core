<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\Condition {

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Condition\Condition
   */
  class ConditionTest extends \Papaya\TestFramework\TestCase {

    public function testConstructor() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Condition_TestProxy($group);
      $this->assertSame($group, $element->getParent());
    }

    public function testGetDatabaseAccess() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);
      $element = new Condition_TestProxy($group);
      $this->assertSame($databaseAccess, $element->getDatabaseAccess());
    }

    public function testGetMapping() {
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
        ->disableOriginalConstructor()
        ->getMock();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getMapping')
        ->willReturn($mapping);
      $element = new Condition_TestProxy($group);
      $this->assertSame($mapping, $element->getMapping());
    }

    public function testGetMappingExpectingNull() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Condition_TestProxy($group);
      $this->assertNull($element->getMapping());
    }

    public function testMagicMethodToString() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Condition_TestProxy($group);
      $element->getSQLHandler = static function() {
        return 'sql string';
      };
      $this->assertEquals(
        'sql string', (string)$element
      );
    }

    /**
     * @covers \Papaya\Database\Condition\Condition
     */
    public function testMapFieldName() {
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
        ->disableOriginalConstructor()
        ->getMock();
      $mapping
        ->expects($this->once())
        ->method('getField')
        ->with('field')
        ->willReturn('mapped_field');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getMapping')
        ->willReturn($mapping);
      $element = new Condition_TestProxy($group);
      $this->assertEquals('mapped_field', $element->mapFieldName('field'));
    }

    public function testMapFieldNameWithoutMapping() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Condition_TestProxy($group);
      $this->assertEquals('field', $element->mapFieldName('field'));
    }

    public function testMapFieldNameWithInvalidMappingExpectingException() {
      $mapping = $this
        ->getMockBuilder(\Papaya\Database\Interfaces\Mapping::class)
        ->disableOriginalConstructor()
        ->getMock();
      $mapping
        ->expects($this->once())
        ->method('getField')
        ->with('field')
        ->willReturn('');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $group
        ->expects($this->once())
        ->method('getMapping')
        ->willReturn($mapping);
      $element = new Condition_TestProxy($group);
      $this->expectException(\LogicException::class);
      $element->mapFieldName('field');
    }

    public function testMapFieldNameWithEmptyFieldNameException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
      $group = $this
        ->getMockBuilder(Group::class)
        ->disableOriginalConstructor()
        ->getMock();
      $element = new Condition_TestProxy($group);
      $this->expectException(\LogicException::class);
      $element->mapFieldName('');
    }
  }

  class Condition_TestProxy extends Condition {

    /**
     * @var callable
     */
    public $getSQLHandler;

    public function mapFieldName($value) {
      return parent::mapFieldName($value);
    }

    public function getSql($silent = FALSE) {
      $handler = $this->getSQLHandler;
      return $handler($silent);
    }
  }
}

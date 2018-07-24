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

use Papaya\Cache\Identifier\Definition\Group;
use Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionGroupTest extends PapayaTestCase {

  /**
   * @covers Group
   */
  public function testGetStatusWithOneDefinitionReturingTrue() {
    $mockDefinition = $this->createMock(Definition::class);
    $mockDefinition
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(TRUE));
    $definition = new Group($mockDefinition);
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers Group
   */
  public function testGetStatusWithTwoDefinitionsReturingFalseSecondNeverCalled() {
    $one = $this->createMock(Definition::class);
    $one
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(FALSE));
    $two = $this->createMock(Definition::class);
    $two
      ->expects($this->never())
      ->method('getStatus');
    $definition = new Group($one, $two);
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers Group
   */
  public function testGetStatusWithTwoDefinitionsMergingReturns() {
    $one = $this->createMock(Definition::class);
    $one
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('foo' => '21')));
    $two = $this->createMock(Definition::class);
    $two
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('bar' => '48')));
    $definition = new Group($one, $two);
    $this->assertEquals(
      array(
        Group::class => array(
           array('foo' => '21'), array('bar' => '48')
        )
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers       Group
   * @dataProvider provideSourceExamples
   * @param int $expected
   * @param int $sourceOne
   * @param int $sourceTwo
   */
  public function testGetSourcesFromTwoDefinitions($expected, $sourceOne, $sourceTwo) {
    $one = $this->createMock(Definition::class);
    $one
      ->expects($this->once())
      ->method('getSources')
      ->will($this->returnValue($sourceOne));
    $two = $this->createMock(Definition::class);
    $two
      ->expects($this->once())
      ->method('getSources')
      ->will($this->returnValue($sourceTwo));
    $definition = new Group($one, $two);
    $this->assertEquals(
      $expected,
      $definition->getSources()
    );
  }

  /**
   * @covers Group
   */
  public function testAdd() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Definition $one */
    $one = $this->createMock(Definition::class);
    $one
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('foo' => '21')));
    /** @var PHPUnit_Framework_MockObject_MockObject|Definition $two */
    $two = $this->createMock(Definition::class);
    $two
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('bar' => '48')));
    $definition = new Group();
    $definition->add($one);
    $definition->add($two);
    $this->assertEquals(
      array(
        Group::class => array(
           array('foo' => '21'), array('bar' => '48')
        )
      ),
      $definition->getStatus()
    );
  }

  public static function provideSourceExamples() {
    return array(
      array(
        Definition::SOURCE_URL,
        Definition::SOURCE_URL,
        Definition::SOURCE_URL
      ),
      array(
        Definition::SOURCE_URL |
          Definition::SOURCE_SESSION,
        Definition::SOURCE_URL,
        Definition::SOURCE_SESSION
      ),
      array(
        Definition::SOURCE_URL |
          Definition::SOURCE_SESSION,
        Definition::SOURCE_URL |
          Definition::SOURCE_SESSION,
        Definition::SOURCE_SESSION
      ),
      array(
        Definition::SOURCE_URL |
          Definition::SOURCE_SESSION |
          Definition::SOURCE_VARIABLES,
        Definition::SOURCE_URL |
          Definition::SOURCE_SESSION,
        Definition::SOURCE_VARIABLES
      ),
    );
  }
}

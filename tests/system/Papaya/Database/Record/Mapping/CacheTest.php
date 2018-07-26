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

use Papaya\Database\Record\Mapping\Cache;
use Papaya\Database\Record\Mapping\Callbacks;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordMappingCacheTest extends PapayaTestCase {

  /**
   * @covers Cache::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $cache = new Cache($mapping);
    $this->assertAttributeSame($mapping, '_mapping', $cache);
  }

  /**
   * @covers Cache::__construct
   */
  public function testConstructorWithCallbacks() {
    $callbacks = $this->getCallbacksMock(
      array(
        'eventOne' => new \PapayaObjectCallback(NULL),
        'eventTwo' => $callbackTwo = new \PapayaObjectCallback('42'),
        'eventThree' => $callbackThree = new \PapayaObjectCallback(NULL)
      )
    );
    $callbackThree->callback = 'substr';
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this
      ->getMockBuilder(Papaya\Database\Record\Mapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('callbacks')
      ->will($this->returnValue($callbacks));
    $cache = new Cache($mapping);
    $this->assertAttributeSame(
      array(
        'eventTwo' => $callbackTwo,
        'eventThree' => $callbackThree
      ),
      '_callbacks',
      $cache
    );
  }

  /**
   * @covers Cache::mapFieldsToProperties
   */
  public function testMapFieldsToProperties() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getProperty')
      ->with('fieldOne')
      ->will($this->returnValue('propertyOne'));
    $cache = new Cache($mapping);
    $this->assertEquals(
      array('propertyOne' => 42),
      $cache->mapFieldsToProperties(
        array('fieldOne' => 42)
      )
    );
  }

  /**
   * @covers Cache::mapFieldsToProperties
   */
  public function testMapFieldsToPropertiesWithCallbacks() {
    $callbacks = $this->getCallbacksMock(
      array(
        'onBeforeMappingFieldsToProperties' => new \PapayaObjectCallback(array()),
        'onBeforeMapping' => new \PapayaObjectCallback(array()),
        'onMapValueFromFieldToProperty' => new \PapayaObjectCallback(0),
        'onMapValue' => new \PapayaObjectCallback(0),
        'onAfterMappingFieldsToProperties' => new \PapayaObjectCallback(array()),
        'onAfterMapping' => new \PapayaObjectCallback(array('propertyTwo' => 23)),
      )
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Record\Mapping $mapping */
    $mapping = $this
      ->getMockBuilder(Papaya\Database\Record\Mapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('callbacks')
      ->will($this->returnValue($callbacks));
    $mapping
      ->expects($this->once())
      ->method('getProperty')
      ->with('fieldOne')
      ->will($this->returnValue('propertyOne'));
    $cache = new Cache($mapping);
    $this->assertEquals(
      array('propertyTwo' => 23),
      $cache->mapFieldsToProperties(
        array('fieldOne' => 42)
      )
    );
  }

  /**
   * @covers Cache::mapPropertiesToFields
   */
  public function testMapPropertiesToFields() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('propertyOne', TRUE)
      ->will($this->returnValue('fieldOne'));
    $cache = new Cache($mapping);
    $this->assertEquals(
      array('fieldOne' => 42),
      $cache->mapPropertiesToFields(
        array('propertyOne' => 42)
      )
    );
  }

  /**
   * @covers Cache::mapPropertiesToFields
   */
  public function testMapPropertiesToFieldsWithCallbacks() {
    $callbacks = $this->getCallbacksMock(
      array(
        'onBeforeMappingPropertiesToFields' => new \PapayaObjectCallback(array()),
        'onBeforeMapping' => new \PapayaObjectCallback(array()),
        'onMapValueFromPropertyToField' => new \PapayaObjectCallback(0),
        'onMapValue' => new \PapayaObjectCallback(0),
        'onAfterMappingPropertiesToFields' => new \PapayaObjectCallback(array()),
        'onAfterMapping' => new \PapayaObjectCallback(array('fieldOne' => 23)),
      )
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Record\Mapping $mapping */
    $mapping = $this
      ->getMockBuilder(Papaya\Database\Record\Mapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('callbacks')
      ->will($this->returnValue($callbacks));
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('propertyTwo')
      ->will($this->returnValue('fieldTwo'));
    $cache = new Cache($mapping);
    $this->assertEquals(
      array('fieldOne' => 23),
      $cache->mapPropertiesToFields(
        array('propertyTwo' => 42), 'alias'
      )
    );
  }

  /**
   * @covers Cache::getProperties
   */
  public function testGetProperties() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getProperties')
      ->will($this->returnValue(array('property')));

    $cache = new Cache($mapping);
    $cache->getProperties();
    $this->assertEquals(array('property'), $cache->getProperties());
  }

  /**
   * @covers Cache::getFields
   */
  public function testGetFields() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->exactly(2))
      ->method('getFields')
      ->will($this->returnArgument(0));

    $cache = new Cache($mapping);
    $this->assertEquals('aliasOne', $cache->getFields('aliasOne'));
    $this->assertEquals('aliasTwo', $cache->getFields('aliasTwo'));
    $this->assertEquals('aliasTwo', $cache->getFields('aliasTwo'));
  }

  /**
   * @covers Cache::getProperty
   */
  public function testGetProperty() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->exactly(2))
      ->method('getProperty')
      ->will($this->returnArgument(0));

    $cache = new Cache($mapping);
    $this->assertEquals('fieldOne', $cache->getProperty('fieldOne'));
    $this->assertEquals('fieldTwo', $cache->getProperty('fieldTwo'));
    $this->assertEquals('fieldTwo', $cache->getProperty('fieldTwo'));
  }

  /**
   * @covers Cache::getField
   */
  public function testGetField() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->exactly(3))
      ->method('getField')
      ->will($this->returnArgument(0));

    $cache = new Cache($mapping);
    $this->assertEquals('fieldOne', $cache->getField('fieldOne'));
    $this->assertEquals('fieldTwo', $cache->getField('fieldTwo'));
    $this->assertEquals('fieldTwo', $cache->getField('fieldTwo', 'alias'));
    $this->assertEquals('fieldTwo', $cache->getField('fieldTwo'));
  }

  private function getCallbacksMock(array $events = array()) {
    $callbacks = $this->createMock(Callbacks::class);
    $callbacks
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($events)));
    return $callbacks;
  }
}

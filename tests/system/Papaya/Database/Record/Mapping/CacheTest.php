<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordMappingCacheTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseRecordMappingCache::__construct
   */
  public function testConstructor() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertAttributeSame($mapping, '_mapping', $cache);
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::__construct
   */
  public function testConstructorWithCallbacks() {
    $callbacks = $this->getCallbacksMock(
      array(
        'eventOne' => new PapayaObjectCallback(NULL),
        'eventTwo' => $callbackTwo = new PapayaObjectCallback('42'),
        'eventThree' => $callbackThree = new PapayaObjectCallback(NULL)
      )
    );
    $callbackThree->callback = 'substr';
    $mapping = $this
      ->getMockBuilder(PapayaDatabaseRecordMapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('callbacks')
      ->will($this->returnValue($callbacks));
    $cache = new PapayaDatabaseRecordMappingCache($mapping);
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
   * @covers PapayaDatabaseRecordMappingCache::mapFieldsToProperties
   */
  public function testMapFieldsToProperties() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('getProperty')
      ->with('fieldOne')
      ->will($this->returnValue('propertyOne'));
    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals(
      array('propertyOne' => 42),
      $cache->mapFieldsToProperties(
        array('fieldOne' => 42)
      )
    );
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::mapFieldsToProperties
   */
  public function testMapFieldsToPropertiesWithCallbacks() {
    $callbacks = $this->getCallbacksMock(
      array(
        'onBeforeMappingFieldsToProperties' => new PapayaObjectCallback(array()),
        'onBeforeMapping' => new PapayaObjectCallback(array()),
        'onMapValueFromFieldToProperty' => new PapayaObjectCallback(0),
        'onMapValue' => new PapayaObjectCallback(0),
        'onAfterMappingFieldsToProperties' => new PapayaObjectCallback(array()),
        'onAfterMapping' => new PapayaObjectCallback(array('propertyTwo' => 23)),
      )
    );
    $mapping = $this
      ->getMockBuilder(PapayaDatabaseRecordMapping::class)
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
    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals(
      array('propertyTwo' => 23),
      $cache->mapFieldsToProperties(
        array('fieldOne' => 42)
      )
    );
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::mapPropertiesToFields
   */
  public function testMapPropertiesToFields() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('propertyOne', TRUE)
      ->will($this->returnValue('fieldOne'));
    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals(
      array('fieldOne' => 42),
      $cache->mapPropertiesToFields(
        array('propertyOne' => 42)
      )
    );
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::mapPropertiesToFields
   */
  public function testMapPropertiesToFieldsWithCallbacks() {
    $callbacks = $this->getCallbacksMock(
      array(
        'onBeforeMappingPropertiesToFields' => new PapayaObjectCallback(array()),
        'onBeforeMapping' => new PapayaObjectCallback(array()),
        'onMapValueFromPropertyToField' => new PapayaObjectCallback(0),
        'onMapValue' => new PapayaObjectCallback(0),
        'onAfterMappingPropertiesToFields' => new PapayaObjectCallback(array()),
        'onAfterMapping' => new PapayaObjectCallback(array('fieldOne' => 23)),
      )
    );
    $mapping = $this
      ->getMockBuilder(PapayaDatabaseRecordMapping::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('callbacks')
      ->will($this->returnValue($callbacks));
    $mapping
      ->expects($this->once())
      ->method('getField', 'alias')
      ->with('propertyTwo')
      ->will($this->returnValue('fieldTwo'));
    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals(
      array('fieldOne' => 23),
      $cache->mapPropertiesToFields(
        array('propertyTwo' => 42), 'alias'
      )
    );
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::getProperties
   */
  public function testGetProperties() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('getProperties')
      ->will($this->returnValue(array('property')));

    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $cache->getProperties();
    $this->assertEquals(array('property'), $cache->getProperties());
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::getFields
   */
  public function testGetFields() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->exactly(2))
      ->method('getFields')
      ->will($this->returnArgument(0));

    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals('aliasOne', $cache->getFields('aliasOne'));
    $this->assertEquals('aliasTwo', $cache->getFields('aliasTwo'));
    $this->assertEquals('aliasTwo', $cache->getFields('aliasTwo'));
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::getProperty
   */
  public function testGetProperty() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->exactly(2))
      ->method('getProperty')
      ->will($this->returnArgument(0));

    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals('fieldOne', $cache->getProperty('fieldOne'));
    $this->assertEquals('fieldTwo', $cache->getProperty('fieldTwo'));
    $this->assertEquals('fieldTwo', $cache->getProperty('fieldTwo'));
  }

  /**
   * @covers PapayaDatabaseRecordMappingCache::getField
   */
  public function testGetField() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->exactly(3))
      ->method('getField')
      ->will($this->returnArgument(0));

    $cache = new PapayaDatabaseRecordMappingCache($mapping);
    $this->assertEquals('fieldOne', $cache->getField('fieldOne'));
    $this->assertEquals('fieldTwo', $cache->getField('fieldTwo'));
    $this->assertEquals('fieldTwo', $cache->getField('fieldTwo', 'alias'));
    $this->assertEquals('fieldTwo', $cache->getField('fieldTwo'));
  }

  private function getCallbacksMock(array $events = array()) {
    $callbacks = $this->createMock(PapayaDatabaseRecordMappingCallbacks::class);
    $callbacks
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($events)));
    return $callbacks;
  }
}

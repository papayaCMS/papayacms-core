<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordMappingTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordMapping::__construct
  * @covers PapayaDatabaseRecordMapping::setDefinition
  * @covers PapayaDatabaseRecordMapping::stripAliasFromField
  */
  public function testConstructor() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      )
    );
    $this->assertAttributeEquals(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      ),
      '_properties',
      $mapping
    );
    $this->assertAttributeEquals(
      array(
        'field_one' => 'property_one',
        'a.field_three' => 'property_three'
      ),
      '_fields',
      $mapping
    );
    $this->assertAttributeEquals(
      array(
        'field_one' => 'property_one',
        'field_three' => 'property_three'
      ),
      '_fieldsWithoutAlias',
      $mapping
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::__construct
  * @covers PapayaDatabaseRecordMapping::setDefinition
  */
  public function testConstructorWithInvalidDefinition() {
    $this->setExpectedException(
      'LogicException',
      'Duplicate database field "field" in mapping definition.'
    );
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field',
        'property_two' => 'field',
        'property_three' => 'a.field_three'
      )
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapFieldsToProperties
  * @covers PapayaDatabaseRecordMapping::getProperty
  */
  public function testMapFieldsToProperties() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      )
    );
    $this->assertEquals(
      array(
        'property_one' => 42,
        'property_three' => 'foo'
      ),
      $mapping->mapFieldsToProperties(
        array(
          'field_one' => 42,
          'field_two' => 21,
          'field_three' => 'foo'
        )
      )
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapPropertiesToFields
  * @covers PapayaDatabaseRecordMapping::getField
  */
  public function testMapPropertiesToFields() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      )
    );
    $this->assertEquals(
      array(
        'field_one' => 42,
        'a.field_three' => 'foo'
      ),
      $mapping->mapPropertiesToFields(
        array(
          'property_one' => 42,
          'property_two' => 21,
          'unknown' => 23,
          'property_three' => 'foo'
        )
      )
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapPropertiesToFields
  * @covers PapayaDatabaseRecordMapping::getField
  */
  public function testMapPropertiesToFieldsWithoutAlias() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      )
    );
    $this->assertEquals(
      array(
        'field_one' => 42,
        'field_three' => 'foo'
      ),
      $mapping->mapPropertiesToFields(
        array(
          'property_one' => 42,
          'property_two' => 21,
          'unknown' => 23,
          'property_three' => 'foo'
        ),
        FALSE
      )
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::getProperties
  */
  public function testGetProperties() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => ''
      )
    );
    $this->assertEquals(
      array('property_one', 'property_two'),
      $mapping->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::getFields
  */
  public function testGetFields() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      )
    );
    $this->assertEquals(
      array('field_one', 'a.field_three'),
      $mapping->getFields()
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::getFields
  */
  public function testGetFieldsWithoutAlias() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'field_one',
        'property_two' => '',
        'property_three' => 'a.field_three'
      )
    );
    $this->assertEquals(
      array('field_one', 'field_three'),
      $mapping->getFields(FALSE)
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::getFields
  */
  public function testGetFieldsLimitedByAlias() {
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'a.field_one',
        'property_two' => 'aa.field_two',
        'property_three' => 'b.field_three',
        'property_four' => 'b.field_four'
      )
    );
    $this->assertEquals(
      array('field_one'),
      $mapping->getFields('a')
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $mapping = new PapayaDatabaseRecordMapping(array());
    $mapping->callbacks($callbacks = $this->getMock('PapayaDatabaseRecordMappingCallbacks'));
    $this->assertSame($callbacks, $mapping->callbacks());
  }

  /**
  * @covers PapayaDatabaseRecordMapping::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $mapping = new PapayaDatabaseRecordMapping(array());
    $this->assertInstanceOf('PapayaDatabaseRecordMappingCallbacks', $mapping->callbacks());
  }

  /**
  * @covers PapayaDatabaseRecordMapping::getField
  * @covers PapayaDatabaseRecordMapping::mapPropertiesToFields
  */
  public function testMapPropertiesToFieldsWithoutCallbacksLimitByAlias() {
    $values = array(
      'property_one' => 42
    );
    $record = array(
      'field_one' => 42
    );
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array('__isset')
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', FALSE),
            array('onBeforeMappingFieldsToProperties', FALSE),
            array('onBeforeMappingPropertiesToFields', FALSE),
            array('onAfterMapping', FALSE),
            array('onAfterMappingFieldsToProperties', FALSE),
            array('onAfterMappingPropertiesToFields', FALSE),
            array('onMapValue', FALSE),
            array('onMapValueFromFieldToProperty', FALSE),
            array('onMapValueFromPropertyToField', FALSE),
            array('onGetPropertyForField', FALSE),
            array('onGetFieldForProperty', FALSE)
          )
        )
      );
    $mapping = new PapayaDatabaseRecordMapping(
      array(
        'property_one' => 'a.field_one',
        'property_two' => 'aa.field_two',
        'property_three' => 'b.field_three',
        'property_four' => 'b.field_four'
      )
    );
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      $record,
      $mapping->mapPropertiesToFields($values, 'a')
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapPropertiesToFields
  */
  public function testMapPropertiesToFieldsGenericCallbacks() {
    $values = array(
      'property_one' => 42
    );
    $record = array(
      'field_one' => 42
    );
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array('__isset', '__get', 'onBeforeMapping', 'onMapValue', 'onAfterMapping')
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', TRUE),
            array('onBeforeMappingFieldsToProperties', FALSE),
            array('onBeforeMappingPropertiesToFields', FALSE),
            array('onAfterMapping', TRUE),
            array('onAfterMappingFieldsToProperties', FALSE),
            array('onAfterMappingPropertiesToFields', FALSE),
            array('onMapValue', TRUE),
            array('onMapValueFromFieldToProperty', FALSE),
            array('onMapValueFromPropertyToField', FALSE),
            array('onGetPropertyForField', FALSE),
            array('onGetFieldForProperty', FALSE)
          )
        )
      );
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('onMapValue', new PapayaObjectCallback(42))
          )
        )
      );
    $callbacks
      ->expects($this->once())
      ->method('onBeforeMapping')
      ->with(PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD, $values, array())
      ->will($this->returnValue(array()));
    $callbacks
      ->expects($this->once())
      ->method('onAfterMapping')
      ->with(PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD, $values, $record)
      ->will($this->returnValue($record));
    $mapping = new PapayaDatabaseRecordMapping(array('property_one' => 'field_one'));
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      $record,
      $mapping->mapPropertiesToFields($values)
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapPropertiesToFields
  */
  public function testMapPropertiesToFieldsSpecificCallbacks() {
    $values = array(
      'property_one' => 42
    );
    $record = array(
      'field_one' => 42
    );
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array(
          '__isset',
          '__get',
          'onBeforeMappingPropertiesToFields',
          'onMapValueFromPropertyToField',
          'onAfterMappingPropertiesToFields'
        )
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('onMapValueFromPropertyToField', new PapayaObjectCallback(42))
          )
        )
      );
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', FALSE),
            array('onBeforeMappingFieldsToProperties', FALSE),
            array('onBeforeMappingPropertiesToFields', TRUE),
            array('onAfterMapping', FALSE),
            array('onAfterMappingFieldsToProperties', FALSE),
            array('onAfterMappingPropertiesToFields', TRUE),
            array('onMapValue', FALSE),
            array('onMapValueFromFieldToProperty', FALSE),
            array('onMapValueFromPropertyToField', TRUE),
            array('onGetPropertyForField', FALSE),
            array('onGetFieldForProperty', FALSE)
          )
        )
      );
    $callbacks
      ->expects($this->once())
      ->method('onBeforeMappingPropertiesToFields')
      ->with($values, array())
      ->will($this->returnValue(array()));
    $callbacks
      ->expects($this->once())
      ->method('onAfterMappingPropertiesToFields')
      ->with($values, $record)
      ->will($this->returnValue($record));
    $mapping = new PapayaDatabaseRecordMapping(array('property_one' => 'field_one'));
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      $record,
      $mapping->mapPropertiesToFields($values)
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapPropertiesToFields
  * @covers PapayaDatabaseRecordMapping::getField
  */
  public function testMapPropertiesToFieldsFieldNameCallback() {
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array('__isset', 'onGetFieldForProperty')
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', FALSE),
            array('onBeforeMappingFieldsToProperties', FALSE),
            array('onBeforeMappingPropertiesToFields', FALSE),
            array('onAfterMapping', FALSE),
            array('onAfterMappingFieldsToProperties', FALSE),
            array('onAfterMappingPropertiesToFields', FALSE),
            array('onMapValue', FALSE),
            array('onMapValueFromFieldToProperty', FALSE),
            array('onMapValueFromPropertyToField', FALSE),
            array('onGetPropertyForField', TRUE),
            array('onGetFieldForProperty', TRUE)
          )
        )
      );
    $callbacks
      ->expects($this->once())
      ->method('onGetFieldForProperty')
      ->with('property_one')
      ->will($this->returnValue('FIELD_ONE'));
    $mapping = new PapayaDatabaseRecordMapping(array('property_one' => 'field_one'));
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      array('FIELD_ONE' => 42),
      $mapping->mapPropertiesToFields(array('property_one' => 42))
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapFieldsToProperties
  */
  public function testMapFieldsToPropertiesGenericCallbacks() {
    $values = array(
      'property_one' => 42
    );
    $record = array(
      'field_one' => 42
    );
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array('__isset', '__get', 'onBeforeMapping', 'onMapValue', 'onAfterMapping')
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('onMapValue', new PapayaObjectCallback(42))
          )
        )
      );
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', TRUE),
            array('onBeforeMappingFieldsToProperties', FALSE),
            array('onBeforeMappingPropertiesToFields', FALSE),
            array('onAfterMapping', TRUE),
            array('onAfterMappingFieldsToProperties', FALSE),
            array('onAfterMappingPropertiesToFields', FALSE),
            array('onMapValue', TRUE),
            array('onMapValueFromFieldToProperty', FALSE),
            array('onMapValueFromPropertyToField', FALSE),
            array('onGetPropertyForField', FALSE),
            array('onGetFieldForProperty', FALSE)
          )
        )
      );
    $callbacks
      ->expects($this->once())
      ->method('onBeforeMapping')
      ->with(PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY, array(), $record)
      ->will($this->returnValue(array()));
    $callbacks
      ->expects($this->once())
      ->method('onAfterMapping')
      ->with(PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY, $values, $record)
      ->will($this->returnValue($values));
    $mapping = new PapayaDatabaseRecordMapping(array('property_one' => 'field_one'));
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      $values,
      $mapping->mapFieldsToProperties($record)
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapFieldsToProperties
  */
  public function testMapFieldsToPropertiesSpecificCallbacks() {
    $values = array(
      'property_one' => 42
    );
    $record = array(
      'field_one' => 42
    );
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array(
          '__isset',
          '__get',
          'onBeforeMappingFieldsToProperties',
          'onMapValueFromFieldToProperty',
          'onAfterMappingFieldsToProperties'
        )
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('onMapValueFromFieldToProperty', new PapayaObjectCallback(42))
          )
        )
      );
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', FALSE),
            array('onBeforeMappingFieldsToProperties', TRUE),
            array('onBeforeMappingPropertiesToFields', FALSE),
            array('onAfterMapping', FALSE),
            array('onAfterMappingFieldsToProperties', TRUE),
            array('onAfterMappingPropertiesToFields', FALSE),
            array('onMapValue', FALSE),
            array('onMapValueFromFieldToProperty', TRUE),
            array('onMapValueFromPropertyToField', FALSE),
            array('onGetPropertyForField', FALSE),
            array('onGetFieldForProperty', FALSE)
          )
        )
      );
    $callbacks
      ->expects($this->once())
      ->method('onBeforeMappingFieldsToProperties')
      ->with(array(), $record)
      ->will($this->returnValue(array()));
    $callbacks
      ->expects($this->once())
      ->method('onAfterMappingFieldsToProperties')
      ->with($values, $record)
      ->will($this->returnValue($values));
    $mapping = new PapayaDatabaseRecordMapping(array('property_one' => 'field_one'));
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      $values,
      $mapping->mapFieldsToProperties($record)
    );
  }

  /**
  * @covers PapayaDatabaseRecordMapping::mapFieldsToProperties
  * @covers PapayaDatabaseRecordMapping::getProperty
  */
  public function testMapFieldsToPropertiesWithPropertyNameCallback() {
    $callbacks = $this
      ->getMockBuilder('PapayaDatabaseRecordMappingCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array('__isset', 'onGetPropertyForField')
      )
      ->getMock();
    $callbacks
      ->expects($this->atLeastOnce())
      ->method('__isset')
      ->will(
        $this->returnValueMap(
          array(
            array('onBeforeMapping', FALSE),
            array('onBeforeMappingFieldsToProperties', FALSE),
            array('onBeforeMappingPropertiesToFields', FALSE),
            array('onAfterMapping', FALSE),
            array('onAfterMappingFieldsToProperties', FALSE),
            array('onAfterMappingPropertiesToFields', FALSE),
            array('onMapValue', FALSE),
            array('onMapValueFromFieldToProperty', FALSE),
            array('onMapValueFromPropertyToField', FALSE),
            array('onGetPropertyForField', TRUE),
            array('onGetFieldForProperty', TRUE)
          )
        )
      );
    $callbacks
      ->expects($this->once())
      ->method('onGetPropertyForField')
      ->with('field_one')
      ->will($this->returnValue('PROPERTY_ONE'));
    $mapping = new PapayaDatabaseRecordMapping(array('property_one' => 'field_one'));
    $mapping->callbacks($callbacks);
    $this->assertEquals(
      array('PROPERTY_ONE' => 42),
      $mapping->mapFieldsToProperties(array('field_one' => 42))
    );
  }
}

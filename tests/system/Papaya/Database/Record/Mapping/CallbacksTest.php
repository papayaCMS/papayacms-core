<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaDatabaseRecordMappingCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordMappingCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaDatabaseRecordMappingCallbacks();
    $this->assertNull($callbacks->onBeforeMapping->defaultReturn);
    $this->assertNull($callbacks->onBeforeMappingFieldsToProperties->defaultReturn);
    $this->assertNull($callbacks->onBeforeMappingPropertiesToFields->defaultReturn);
    $this->assertNull($callbacks->onAfterMapping->defaultReturn);
    $this->assertNull($callbacks->onAfterMappingFieldsToProperties->defaultReturn);
    $this->assertNull($callbacks->onAfterMappingPropertiesToFields->defaultReturn);
    $this->assertNull($callbacks->onMapValue->defaultReturn);
    $this->assertNull($callbacks->onMapValueFromFieldToProperty->defaultReturn);
    $this->assertNull($callbacks->onMapValueFromPropertyToField->defaultReturn);
    $this->assertNull($callbacks->onGetPropertyForField->defaultReturn);
    $this->assertNull($callbacks->onGetFieldForProperty->defaultReturn);
  }
}
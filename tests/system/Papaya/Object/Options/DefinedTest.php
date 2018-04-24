<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaObjectOptionsDefinedTest extends PapayaTestCase {

  /**
  * @covers PapayaObjectOptionsDefined::toArray
  */
  public function testToArray() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->assertEquals(
      array(
       'VALID_OPTION' => TRUE
      ),
      $options->toArray()
    );
  }
  /**
  * @covers PapayaObjectOptionsDefined::count
  */
  public function testCount() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->assertEquals(
      1,
      count($options)
    );
  }

  /**
  * @covers PapayaObjectOptionsDefined::_write
  */
  public function testSetOption() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $options->validOption = FALSE;
    $this->assertAttributeEquals(
      array(
       'VALID_OPTION' => FALSE
      ),
      '_options',
      $options
    );
  }

  /**
  * @covers PapayaObjectOptionsDefined::_write
  */
  public function testSetOptionExpectingException() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $options->invalidOption = FALSE;
  }

  /**
  * @covers PapayaObjectOptionsDefined::_read
  */
  public function testGetOptionAfterSet() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $options->validOption = FALSE;
    $this->assertFalse($options->validOption);
  }

  /**
  * @covers PapayaObjectOptionsDefined::_read
  */
  public function testGetOptionReadingDefault() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->assertTrue($options->validOption);
  }

  /**
  * @covers PapayaObjectOptionsDefined::_read
  */
  public function testGetOptionExpectingException() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $dummy = $options->invalidOption;
  }

  /**
  * @covers PapayaObjectOptionsDefined::_exists
  */
  public function testIssetOptionExpectingTrue() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->assertTrue(isset($options->validOption));
  }

  /**
  * @covers PapayaObjectOptionsDefined::_exists
  */
  public function testIssetOptionExpectingFalse() {
    $options = new PapayaObjectOptionsDefined_TestProxy();
    $this->assertFalse(isset($options->invalidOption));
  }

}

class PapayaObjectOptionsDefined_TestProxy extends PapayaObjectOptionsDefined {

  protected $_definitions = array(
    'VALID_OPTION' => array(TRUE, FALSE)
  );

}

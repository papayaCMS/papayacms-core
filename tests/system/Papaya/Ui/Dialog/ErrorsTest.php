<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiDialogErrorsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogErrors::getIterator
  */
  public function testIterator() {
    $errors = new PapayaUiDialogErrors();
    $errors->add(new Exception(), new stdClass());
    $errors->add(new Exception(), new stdClass());
    $result = array();
    foreach ($errors as $index => $error) {
      $result[$index] = get_class($error['exception']).','.get_class($error['source']);
    }
    $this->assertEquals(
      array('Exception,stdClass', 'Exception,stdClass'),
      $result
    );
  }

  /**
  * @covers PapayaUiDialogErrors::count
  */
  public function testCountable() {
    $errors = new PapayaUiDialogErrors();
    $errors->add(new Exception(), new stdClass());
    $this->assertEquals(1, count($errors));
  }

  /**
  * @covers PapayaUiDialogErrors::add
  */
  public function testAddWithoutSource() {
    $errors = new PapayaUiDialogErrors();
    $errors->add($e = new Exception());
    $this->assertAttributeEquals(
      array(
        array(
          'exception' => $e,
          'source' => NULL
        )
      ),
      '_errors',
      $errors
    );
  }

  /**
  * @covers PapayaUiDialogErrors::add
  */
  public function testAddWithSource() {
    $errors = new PapayaUiDialogErrors();
    $errors->add($e = new Exception(), $source = new stdClass());
    $this->assertAttributeEquals(
      array(
        array(
          'exception' => $e,
          'source' => $source
        )
      ),
      '_errors',
      $errors
    );
  }

  /**
  * @covers PapayaUiDialogErrors::clear
  */
  public function testClear() {
    $errors = new PapayaUiDialogErrors();
    $errors->clear();
    $this->assertAttributeEquals(
      array(),
      '_errors',
      $errors
    );
  }

  /**
  * @covers PapayaUiDialogErrors::getSourceCaptions
  */
  public function testGetSourceCaptions() {
    $errors = new PapayaUiDialogErrors();
    $fieldOne = $this->getMock('PapayaUiDialogField', array('getCaption', 'appendTo'));
    $fieldOne
      ->expects($this->once())
      ->method('getCaption')
      ->will($this->returnValue('FieldOne'));
    $fieldTwo = $this->getMock('PapayaUiDialogField', array('getCaption', 'appendTo'));
    $fieldTwo
      ->expects($this->once())
      ->method('getCaption')
      ->will($this->returnValue('FieldTwo'));
    $errors->add(new Exception(), $fieldOne);
    $errors->add(new Exception()); // invalid (empty) source, without caption
    $errors->add(new Exception(), $fieldTwo);
    $this->assertEquals(
      array('FieldOne', 'FieldTwo'), $errors->getSourceCaptions()
    );
  }
}
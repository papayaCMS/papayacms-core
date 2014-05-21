<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldInputTimeTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputTime::__construct
  */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldInputTime('Time', 'time', '00:00:00', TRUE, 300.0);
    $this->assertEquals('Time', $input->caption);
    $this->assertEquals('time', $input->name);
    $this->assertEquals('00:00:00', $input->defaultValue);
    $this->assertTrue($input->mandatory);
    $this->assertAttributeEquals(300.0, '_step', $input);
  }

  /**
  * @covers PapayaUiDialogFieldInputTime::__construct
  */
  public function testConstructorWithInvalidStep() {
    try {
      $input = new PapayaUiDialogFieldInputTime('Time', 'time', '00:00:00', TRUE, -300.0);
    } catch (InvalidArgumentException $e) {
      $this->assertEquals(
        'Step must not be less than 0.',
        $e->getMessage()
      );
    }
  }

  /**
  * @covers PapayaUiDialogFieldInputTime
  * @dataProvider filterExpectingTrueProvider
  */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new PapayaUiDialogFieldInputTime('Time', 'time');
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputTime
  * @dataProvider filterExpectingFalseProvider
  */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new PapayaUiDialogFieldInputTime('Time', 'time');
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputTime::getXml
  */
  public function testGetXml() {
    $input = new PapayaUiDialogFieldInputTime('Time', 'time');
    $input->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<field caption="Time" class="DialogFieldInputTime" error="no">'.
        '<input type="time" name="time" maxlength="9"/>'.
      '</field>',
      $input->getXml()
    );
  }

  public static function filterExpectingTrueProvider() {
    return array(
      array('18:35', TRUE),
      array('18:35', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('18X35', TRUE),
      array('18X35', FALSE),
      array('', TRUE)
    );
  }
}
<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterStringNormalizeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterStringNormalize
   */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterStringNormalize();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers PapayaFilterStringNormalize
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new PapayaFilterStringNormalize();
    $this->setExpectedException('PapayaFilterExceptionEmpty');
    $filter->validate('');
  }

  /**
   * @covers PapayaFilterStringNormalize
   */
  public function testValidateWithArrayValueExpectingException() {
    $filter = new PapayaFilterStringNormalize();
    $this->setExpectedException('PapayaFilterExceptionType');
    $filter->validate(['foo']);
  }

  /**
   * @covers PapayaFilterStringNormalize
   * @dataProvider provideValuesToNormalize
   */
  public function testFilter($expected, $provided, $options = 0) {
    $filter = new PapayaFilterStringNormalize($options);
    $this->assertSame($expected, $filter->filter($provided));
  }

  public static function provideValuesToNormalize() {
    return [
      [NULL, ''],
      [NULL, []],
      ['trim', ' trim '],
      ['123', ' 123 '],
      ['Keep UpperCase', ' Keep   UpperCase '],
      ['to lowercase', ' To   LowerCase ', PapayaFilterStringNormalize::OPTION_LOWERCASE]
    ];
  }
}

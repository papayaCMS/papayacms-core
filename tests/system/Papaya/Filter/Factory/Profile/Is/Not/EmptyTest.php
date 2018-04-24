<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsNotEmptyTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsNotEmpty::getFilter
   * @dataProvider provideNotEmptyStrings
   */
  public function testGetFilterExpectTrue($string) {
    $profile = new PapayaFilterFactoryProfileIsNotEmpty();
    $this->assertTrue($profile->getFilter()->validate($string));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsNotEmpty::getFilter
   * @dataProvider provideEmptyStrings
   */
  public function testGetFilterExpectException($string) {
    $profile = new PapayaFilterFactoryProfileIsNotEmpty();
    $this->expectException(PapayaFilterException::class);
    $profile->getFilter()->validate($string);
  }

  public static function provideNotEmptyStrings() {
    return array(
      array('0'),
      array('42'),
      array('foo'),
      array(' bar '),
      array('bla blub'),
    );
  }

  public static function provideEmptyStrings() {
    return array(
      array(''),
      array(' '),
      array('  '),
      array("\t")
    );
  }
}

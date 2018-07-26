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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterXmlTest extends \PapayaTestCase {

  /**
   * @covers \PapayaFilterXml::__construct
   */
  public function testConstructorWihtAllArguments() {
    $filter = new \PapayaFilterXml(FALSE);
    $this->assertAttributeEquals(
      FALSE, '_allowFragments', $filter
    );
  }

  /**
   * @covers \PapayaFilterXml::validate
   * @dataProvider provideValidXmlFragments
   * @param string $fragment
   * @throws \PapayaFilterExceptionEmpty
   * @throws \PapayaFilterExceptionXml
   */
  public function testValidate($fragment) {
    $filter = new \PapayaFilterXml();
    $this->assertTrue($filter->validate($fragment));
  }

  /**
   * @covers \PapayaFilterXml::validate
   */
  public function testValidateWithDocument() {
    $filter = new \PapayaFilterXml(FALSE);
    $this->assertTrue($filter->validate(/** @lang XML */'<html/>'));
  }

  /**
   * @covers \PapayaFilterXml::validate
   * @dataProvider provideInvalidXmlFragments
   * @param mixed $fragment
   * @throws \PapayaFilterExceptionEmpty
   * @throws \PapayaFilterExceptionXml
   */
  public function testValidateExpectingException($fragment) {
    $filter = new \PapayaFilterXml();
    $this->expectException(\PapayaFilterExceptionXml::class);
    $filter->validate($fragment);
  }

  /**
   * @covers \PapayaFilterXml::validate
   */
  public function testValidateWithEmptyStringExpectingException() {
    $filter = new \PapayaFilterXml();
    $this->expectException(\PapayaFilterExceptionEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \PapayaFilterXml::validate
   */
  public function testValidateWithDocumentExpectingException() {
    $filter = new \PapayaFilterXml(FALSE);
    $this->expectException(\PapayaFilterExceptionXml::class);
    $filter->validate('TEXT');
  }

  /**
   * @covers \PapayaFilterXml::filter
   * @dataProvider provideValidXmlFragments
   * @param string $fragment
   */
  public function testFilter($fragment) {
    $filter = new \PapayaFilterXml();
    $this->assertEquals($fragment, $filter->filter($fragment));
  }

  /**
   * @covers \PapayaFilterXml::filter
   * @dataProvider provideInvalidXmlFragments
   * @param mixed $fragment
   */
  public function testFilterExpectingNull($fragment) {
    $filter = new \PapayaFilterXml();
    $this->assertNull($filter->filter($fragment));
  }

  public static function provideValidXmlFragments() {
    return array(
      array('<p>Test</p>'),
      array('<p>Test</p><p>Test</p>'),
      array('Test')
    );
  }

  public static function provideInvalidXmlFragments() {
    return array(
      array('<p>Test'),
      array('Test</p>'),
      array('>Test<'),
      array('<Test<')
    );
  }
}

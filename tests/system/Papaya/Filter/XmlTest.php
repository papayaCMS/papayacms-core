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
   * @covers \Papaya\Filter\Xml::__construct
   */
  public function testConstructorWihtAllArguments() {
    $filter = new \Papaya\Filter\Xml(FALSE);
    $this->assertAttributeEquals(
      FALSE, '_allowFragments', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\Xml::validate
   * @dataProvider provideValidXmlFragments
   * @param string $fragment
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidXml
   */
  public function testValidate($fragment) {
    $filter = new \Papaya\Filter\Xml();
    $this->assertTrue($filter->validate($fragment));
  }

  /**
   * @covers \Papaya\Filter\Xml::validate
   */
  public function testValidateWithDocument() {
    $filter = new \Papaya\Filter\Xml(FALSE);
    $this->assertTrue($filter->validate(/** @lang XML */'<html/>'));
  }

  /**
   * @covers \Papaya\Filter\Xml::validate
   * @dataProvider provideInvalidXmlFragments
   * @param mixed $fragment
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidXml
   */
  public function testValidateExpectingException($fragment) {
    $filter = new \Papaya\Filter\Xml();
    $this->expectException(\Papaya\Filter\Exception\InvalidXml::class);
    $filter->validate($fragment);
  }

  /**
   * @covers \Papaya\Filter\Xml::validate
   */
  public function testValidateWithEmptyStringExpectingException() {
    $filter = new \Papaya\Filter\Xml();
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \Papaya\Filter\Xml::validate
   */
  public function testValidateWithDocumentExpectingException() {
    $filter = new \Papaya\Filter\Xml(FALSE);
    $this->expectException(\Papaya\Filter\Exception\InvalidXml::class);
    $filter->validate('TEXT');
  }

  /**
   * @covers \Papaya\Filter\Xml::filter
   * @dataProvider provideValidXmlFragments
   * @param string $fragment
   */
  public function testFilter($fragment) {
    $filter = new \Papaya\Filter\Xml();
    $this->assertEquals($fragment, $filter->filter($fragment));
  }

  /**
   * @covers \Papaya\Filter\Xml::filter
   * @dataProvider provideInvalidXmlFragments
   * @param mixed $fragment
   */
  public function testFilterExpectingNull($fragment) {
    $filter = new \Papaya\Filter\Xml();
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

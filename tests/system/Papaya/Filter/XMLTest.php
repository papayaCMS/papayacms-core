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

namespace Papaya\Filter;
require_once __DIR__.'/../../../bootstrap.php';

/**
 * @covers \Papaya\Filter\XML
 */
class XMLTest extends \Papaya\TestCase {

  /**
   * @dataProvider provideValidXmlFragments
   * @param string $fragment
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidXML
   */
  public function testValidate($fragment) {
    $filter = new XML();
    $this->assertTrue($filter->validate($fragment));
  }

  public function testValidateWithDocument() {
    $filter = new XML(FALSE);
    $this->assertTrue($filter->validate(/** @lang XML */
      '<html/>'));
  }

  /**
   * @dataProvider provideInvalidXmlFragments
   * @param mixed $fragment
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidXML
   */
  public function testValidateExpectingException($fragment) {
    $filter = new XML();
    $this->expectException(\Papaya\Filter\Exception\InvalidXML::class);
    $filter->validate($fragment);
  }

  public function testValidateWithEmptyStringExpectingException() {
    $filter = new XML();
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate('');
  }

  public function testValidateWithDocumentExpectingException() {
    $filter = new XML(FALSE);
    $this->expectException(\Papaya\Filter\Exception\InvalidXML::class);
    $filter->validate('TEXT');
  }

  /**
   * @dataProvider provideValidXmlFragments
   * @param string $fragment
   */
  public function testFilter($fragment) {
    $filter = new XML();
    $this->assertEquals($fragment, $filter->filter($fragment));
  }

  /**
   * @dataProvider provideInvalidXmlFragments
   * @param mixed $fragment
   */
  public function testFilterExpectingNull($fragment) {
    $filter = new XML();
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

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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputPageTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Input\Page::__construct
  */
  public function testConstructor() {
    $field = new \Papaya\UI\Dialog\Field\Input\Page('Page', 'page_id', 42, TRUE);
    $this->assertEquals(
      'Page', $field->caption
    );
    $this->assertEquals(
      'page_id', $field->name
    );
    $this->assertEquals(
      42, $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Page
   * @dataProvider provideValidPageIdInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new \Papaya\UI\Dialog\Field\Input\Page('Page', 'page_id');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Page
   * @dataProvider provideInvalidPageIdInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new \Papaya\UI\Dialog\Field\Input\Page('Page', 'page_id');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input\Page::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $field = new \Papaya\UI\Dialog\Field\Input\Page('Page', 'page_id');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Page" class="DialogFieldInputPage" error="no">
          <input type="page" name="page_id" maxlength="20"/>
        </field>
      </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidPageIdInputs() {
    return array(
      array(1, TRUE),
      array(1, FALSE),
      array(0, FALSE),
      array(NULL, FALSE)
    );
  }

  public static function provideInvalidPageIdInputs() {
    return array(
      array(0, TRUE),
      array(-1, TRUE),
      array(-1, FALSE),
      array(NULL, TRUE)
    );
  }
}

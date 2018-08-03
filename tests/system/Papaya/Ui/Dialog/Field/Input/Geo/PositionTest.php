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

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputGeoPositionTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Input\GeoPosition::__construct
  */
  public function testConstructor() {
    $field = new \Papaya\UI\Dialog\Field\Input\GeoPosition('Position', 'geo_position', '21,42', TRUE);
    $this->assertEquals(
      'Position', $field->caption
    );
    $this->assertEquals(
      'geo_position', $field->name
    );
    $this->assertEquals(
      '21,42', $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\GeoPosition
   * @dataProvider provideValidGeoPositionInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new \Papaya\UI\Dialog\Field\Input\GeoPosition(
      'Position', 'geo_position', $value, $mandatory
    );
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Page
   * @dataProvider provideInvalidGeoPositionInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new \Papaya\UI\Dialog\Field\Input\GeoPosition(
      'Position', 'geo_position', $value, $mandatory
    );
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input\Page::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $field = new \Papaya\UI\Dialog\Field\Input\GeoPosition('Position', 'geo_position', '', FALSE);
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Position" class="DialogFieldInputGeoPosition" error="no">
          <input type="geoposition" name="geo_position" maxlength="100"/>
        </field>
      </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidGeoPositionInputs() {
    return array(
      array('1,1', TRUE),
      array('1,1', FALSE),
      array('', FALSE),
      array(NULL, FALSE)
    );
  }

  public static function provideInvalidGeoPositionInputs() {
    return array(
      array('0', TRUE),
      array('-1', TRUE),
      array('-1', FALSE),
      array(NULL, TRUE)
    );
  }
}

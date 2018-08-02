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

class PapayaUiDialogFieldInputReadonlyTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Readonly::__construct
   */
  public function testConstructor() {
    $input = new \Papaya\Ui\Dialog\Field\Input\Readonly('Caption', 'name');

    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Readonly::__construct
   */
  public function testConstructorWithAllParameters() {
    $input = new \Papaya\Ui\Dialog\Field\Input\Readonly('Caption', 'name', 'default');

    $this->assertAttributeEquals(
      'default', '_defaultValue', $input
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Readonly::appendTo
   */
  public function testStandardAppendTo() {
    $document = new \Papaya\Xml\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);

    $input = new \Papaya\Ui\Dialog\Field\Input\Readonly('Caption', 'name');
    $input->appendTo($node);

    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInputReadonly" error="no">
          <input type="text" name="name" readonly="yes"/>
        </field>
      </sample>',
      $document->saveXML($node)
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Readonly::appendTo
   */
  public function testWithDefaultAppendTo() {
    $document = new \Papaya\Xml\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);

    $input = new \Papaya\Ui\Dialog\Field\Input\Readonly('Caption', 'name', 'default');
    $input->appendTo($node);

    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInputReadonly" error="no">
          <input type="text" name="name" readonly="yes">default</input>
        </field>
      </sample>',
      $document->saveXML($node)
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Readonly::getCurrentValue
   */
  public function testGetCurrentValue() {
    $input = new \Papaya\Ui\Dialog\Field\Input\Readonly('Caption', 'name', 'default');

    $this->assertEquals(
      'default',
      $input->getCurrentValue()
    );
  }
}

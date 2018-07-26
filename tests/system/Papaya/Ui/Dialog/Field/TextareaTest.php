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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldTextareaTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldTextarea::__construct
  */
  public function testConstructor() {
    $textarea = new \PapayaUiDialogFieldTextarea('Caption', 'name');
    $this->assertAttributeEquals(
      'Caption', '_caption', $textarea
    );
    $this->assertAttributeEquals(
      'name', '_name', $textarea
    );
  }

  /**
  * @covers \PapayaUiDialogFieldTextarea::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = $this->createMock(Papaya\Filter::class);
    $textarea = new \PapayaUiDialogFieldTextarea('Caption', 'name', 42, '50670', $filter);
    $this->assertAttributeEquals(
      42, '_lineCount', $textarea
    );
    $this->assertAttributeEquals(
      '50670', '_defaultValue', $textarea
    );
    $this->assertAttributeSame(
      $filter, '_filter', $textarea
    );
  }

  /**
  * @covers \PapayaUiDialogFieldTextarea::setLineCount
  */
  public function testSetLineCount() {
    $textarea = new \PapayaUiDialogFieldTextarea('Caption', 'name');
    $textarea->setLineCount(42);
    $this->assertAttributeEquals(
      42, '_lineCount', $textarea
    );
  }

  /**
  * @covers \PapayaUiDialogFieldTextarea::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $textarea = new \PapayaUiDialogFieldTextarea('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $textarea->papaya($application);
    $textarea->collection($this->createMock(\PapayaUiDialogFields::class));
    $textarea->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldTextarea" error="no">
        <textarea type="text" name="name" lines="10"/>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \PapayaUiDialogFieldTextarea::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $textarea = new \PapayaUiDialogFieldTextarea('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $textarea->papaya($application);
    $textarea->collection($this->createMock(\PapayaUiDialogFields::class));
    $textarea->setDefaultValue(50670);
    $textarea->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldTextarea" error="no">
        <textarea type="text" name="name" lines="10">50670</textarea>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }
}

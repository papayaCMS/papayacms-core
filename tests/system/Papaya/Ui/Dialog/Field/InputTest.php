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

class PapayaUiDialogFieldInputTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::__construct
  */
  public function testConstructor() {
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = $this->createMock(\Papaya\Filter::class);
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name', 42, '50670', $filter);
    $this->assertAttributeEquals(
      42, '_maximumLength', $input
    );
    $this->assertAttributeEquals(
      '50670', '_defaultValue', $input
    );
    $this->assertAttributeSame(
      $filter, '_filter', $input
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::setMaximumLength
  */
  public function testSetMaximumLength() {
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $input->setMaximumLength(42);
    $this->assertAttributeEquals(
      42, '_maximumLength', $input
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::setMaximumLength
  */
  public function testSetMaximumLengthToZeroExpectingMinusOne() {
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $input->setMaximumLength(0);
    $this->assertAttributeEquals(
      -1, '_maximumLength', $input
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::setType
  * @covers \Papaya\UI\Dialog\Field\Input::getType
  */
  public function testGetTypeAfterSetType() {
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $input->setType('email');
    $this->assertEquals('email', $input->getType());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInput" error="no">
        <input type="text" name="name" maxlength="1024"/>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->setDefaultValue(50670);
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInput" error="no">
        <input type="text" name="name" maxlength="1024">50670</input>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input::appendTo
  */
  public function testAppendToAffectedBySetType() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new \Papaya\UI\Dialog\Field\Input('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->setType('email');
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInput" error="no">
        <input type="email" name="name" maxlength="1024"/>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }
}

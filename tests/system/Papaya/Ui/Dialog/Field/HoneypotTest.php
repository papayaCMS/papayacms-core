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

class PapayaUiDialogFieldHoneypotTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldHoneypot::__construct
  */
  public function testConstructor() {
    $input = new \PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
  }

  /**
  * @covers \PapayaUiDialogFieldHoneypot::setFilter
  */
  public function testSetFilterExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $input = new \PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->expectException(LogicException::class);
    $input->setFilter($filter);
  }

  /**
  * @covers \PapayaUiDialogFieldHoneypot::setMandatory
  */
  public function testSetMandatoryExpectingException() {
    $input = new \PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->expectException(LogicException::class);
    $input->setMandatory(FALSE);
  }

  /**
  * @covers \PapayaUiDialogFieldHoneypot
  */
  public function testAppendTo() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('name' => ''))));
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with('name')
      ->will($this->returnValue(new \Papaya\Request\Parameters\Name('name')));
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->withAnyParameters()
      ->will($this->returnValue('group'));
    $collection = $this
      ->getMockBuilder(\PapayaUiDialogFields::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->any())
      ->method('owner')
      ->will($this->returnValue($dialog));

    $input = new \PapayaUiDialogFieldHoneypot('Caption', 'name');
    $input->papaya($this->mockPapaya()->application());
    $input->collection($collection);

    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldHoneypot" error="no" mandatory="yes">
        <input type="text" name="group[name]"/>
      </field>',
      $input->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldHoneypot
  */
  public function testAppendToExpectingError() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array())));
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with('name')
      ->will($this->returnValue(new \Papaya\Request\Parameters\Name('name')));
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->withAnyParameters()
      ->will($this->returnValue(NULL));
    $collection = $this
      ->getMockBuilder(\PapayaUiDialogFields::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->any())
      ->method('owner')
      ->will($this->returnValue($dialog));

    $input = new \PapayaUiDialogFieldHoneypot('Caption', 'name');
    $input->papaya($this->mockPapaya()->application());
    $input->collection($collection);

    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldHoneypot" error="yes" mandatory="yes">
        <input type="text" name="name"/>
      </field>',
      $input->getXml()
    );
  }


  /**
  * @covers \PapayaUiDialogFieldHoneypot
  */
  public function testAppendToWithoutCollection() {
    $input = new \PapayaUiDialogFieldHoneypot('Caption', 'name');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldHoneypot" error="no" mandatory="yes">
        <input type="text" name="name"/>
      </field>',
      $input->getXml()
    );
  }
}

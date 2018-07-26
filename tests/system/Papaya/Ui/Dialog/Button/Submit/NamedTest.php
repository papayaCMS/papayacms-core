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

class PapayaUiDialogButtonSubmitNamedTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::__construct
  */
  public function testConstructor() {
    $button = new \PapayaUiDialogButtonSubmitNamed('Test', 'name');
    $this->assertAttributeEquals(
      'name', '_name', $button
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::__construct
  */
  public function testConstructorWithAllParameters() {
    $button = new \PapayaUiDialogButtonSubmitNamed(
      'Test', 'name', 'value', \PapayaUiDialogButton::ALIGN_LEFT
    );
    $this->assertAttributeEquals(
      'value', '_value', $button
    );
    $this->assertAttributeEquals(
      \PapayaUiDialogButton::ALIGN_LEFT, '_align', $button
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('test');
    $button = new \PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock());
    $button->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><button type="submit" align="right" name="buttonname[1]">Test Caption</button></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::appendTo
  */
  public function testAppendToWithDialogParameterGroup() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with(array('buttonname', 1))
      ->will($this->returnValue(new \PapayaRequestParametersName('buttonname[1]')));
    $document = new \PapayaXmlDocument();
    $document->appendElement('test');
    $button = new \PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $button->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test>
        <button type="submit" align="right" name="group[buttonname][1]">Test Caption</button>
      </test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::collect
  */
  public function testCollectExpectingTrue() {
    $parameters = $this->createMock(PapayaRequestParameters::class);
    $parameters
      ->expects($this->once())
      ->method('has')
      ->with($this->equalTo('buttonname[42]'))
      ->will($this->returnValue(TRUE));
    $data =  $this->createMock(PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('buttonname'), $this->equalTo(42));
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $dialog
      ->expects($this->any())
      ->method('data')
      ->will($this->returnValue($data));
    $button = new \PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname', 42);
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $this->assertTrue($button->collect());
  }

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::collect
  */
  public function testCollectWithGroupExpectingTrue() {
    $parameters = $this->createMock(PapayaRequestParameters::class);
    $parameters
      ->expects($this->once())
      ->method('has')
      ->with($this->equalTo('buttonname[42]'))
      ->will($this->returnValue(TRUE));
    $data =  $this->createMock(PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('buttonname'), $this->equalTo(42));
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with(array('buttonname', 42))
      ->will($this->returnValue(new \PapayaRequestParametersName('buttonname[42]')));
    $dialog
      ->expects($this->any())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $dialog
      ->expects($this->any())
      ->method('data')
      ->will($this->returnValue($data));
    $button = new \PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname', 42);
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $this->assertTrue($button->collect());
    $this->assertEquals(
      /** @lang XML */
      '<button type="submit" align="right" name="group[buttonname][42]">Test Caption</button>',
      $button->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogButtonSubmitNamed::collect
  */
  public function testCollectExpectingFalse() {
    $parameters = $this->createMock(PapayaRequestParameters::class);
    $parameters
      ->expects($this->once())
      ->method('has')
      ->with($this->equalTo('buttonname[42]'))
      ->will($this->returnValue(FALSE));
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $dialog
      ->expects($this->never())
      ->method('data');
    $button = new \PapayaUiDialogButtonSubmitNamed('Test Caption', 'buttonname', 42);
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $button->papaya($application);
    $button->collection($this->getCollectionMock($dialog));
    $this->assertFalse($button->collect());
  }

  /*****************************
  * Mocks
  *****************************/

  /**
   * @param object|NULL $owner
   * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaUiDialogElements
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(PapayaUiDialogElements::class);
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}

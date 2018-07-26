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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaTemplateXsltHandlerTest extends PapayaTestCase {

  /**
  * @covers \PapayaTemplateXsltHandler::getLocalPath
  */
  public function testGetLocalPath() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_PATH_TEMPLATES' => '/path/',
             'PAPAYA_LAYOUT_TEMPLATES' => 'template'
            )
          )
        )
      )
    );
    $this->assertEquals(
      '/path/template/',
      $handler->getLocalPath()
    );
  }

  /**
  * @covers \PapayaTemplateXsltHandler::getTemplate
  */
  public function testGetTemplateInPublicMode() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_LAYOUT_TEMPLATES' => 'template'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'template',
      $handler->getTemplate()
    );
  }

  /**
  * @covers \PapayaTemplateXsltHandler::getTemplate
  */
  public function testGetTemplateInPreviewMode() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(TRUE));
    $session = $this->createMock(PapayaSession::class);
    $values = $this->getMockBuilder(PapayaSessionValues::class)->disableOriginalConstructor()->getMock();
    $values
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('PapayaPreviewTemplate'))
      ->will($this->returnValue('TemplateFromSession'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Session' => $session,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_LAYOUT_TEMPLATES' => 'template'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'TemplateFromSession',
      $handler->getTemplate()
    );
  }

  /**
  * @covers \PapayaTemplateXsltHandler::setTemplatePreview
  */
  public function testSetTemplatePreview() {
    $session = $this->createMock(PapayaSession::class);
    $values = $this->getMockBuilder(PapayaSessionValues::class)->disableOriginalConstructor()->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTemplate'), $this->equalTo('Sample'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->setTemplatePreview('Sample');
  }

  /**
  * @covers \PapayaTemplateXsltHandler::removeTemplatePreview
  */
  public function testRemoveTemplatePreview() {
    $session = $this->createMock(PapayaSession::class);
    $values = $this->getMockBuilder(PapayaSessionValues::class)->disableOriginalConstructor()->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTemplate'), $this->equalTo(NULL));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->removeTemplatePreview();
  }


}

<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaTemplateXsltHandlerTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateXsltHandler::getLocalPath
  */
  public function testGetLocalPath() {
    $request = $this->getMock('PapayaRequest', array('getParameter'));
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new PapayaTemplateXsltHandler();
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
  * @covers PapayaTemplateXsltHandler::getTemplate
  */
  public function testGetTemplateInPublicMode() {
    $request = $this->getMock('PapayaRequest', array('getParameter'));
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new PapayaTemplateXsltHandler();
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
  * @covers PapayaTemplateXsltHandler::getTemplate
  */
  public function testGetTemplateInPreviewMode() {
    $request = $this->getMock('PapayaRequest', array('getParameter'));
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(TRUE));
    $session = $this->getMock('PapayaSession', array('__get'));
    $values = $this->getMock('PapayaSessionValues', array('get'), array($session));
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
    $handler = new PapayaTemplateXsltHandler();
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
  * @covers PapayaTemplateXsltHandler::setTemplatePreview
  */
  public function testSetTemplatePreview() {
    $session = $this->getMock('PapayaSession', array('__get'));
    $values = $this->getMock('PapayaSessionValues', array('set'), array($session));
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTemplate'), $this->equalTo('Sample'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->setTemplatePreview('Sample');
  }

  /**
  * @covers PapayaTemplateXsltHandler::removeTemplatePreview
  */
  public function testRemoveTemplatePreview() {
    $session = $this->getMock('PapayaSession', array('__get'));
    $values = $this->getMock('PapayaSessionValues', array('set'), array($session));
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTemplate'), $this->equalTo(NULL));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new PapayaTemplateXsltHandler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->removeTemplatePreview('Sample');
  }


}
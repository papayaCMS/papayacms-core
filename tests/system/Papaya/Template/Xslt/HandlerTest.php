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

namespace Papaya\Template\Xslt;
require_once __DIR__.'/../../../../bootstrap.php';

class HandlerTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Template\Xslt\Handler::getLocalPath
   */
  public function testGetLocalPath() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new Handler();
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
   * @covers \Papaya\Template\Xslt\Handler::getTemplate
   */
  public function testGetTemplateInPublicMode() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new Handler();
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
   * @covers \Papaya\Template\Xslt\Handler::getTemplate
   */
  public function testGetTemplateInPreviewMode() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(TRUE));
    $session = $this->createMock(\Papaya\Session::class);
    $values = $this->getMockBuilder(\Papaya\Session\Values::class)->disableOriginalConstructor()->getMock();
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
    $handler = new Handler();
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
   * @covers \Papaya\Template\Xslt\Handler::setTemplatePreview
   */
  public function testSetTemplatePreview() {
    $session = $this->createMock(\Papaya\Session::class);
    $values = $this->getMockBuilder(\Papaya\Session\Values::class)->disableOriginalConstructor()->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTemplate'), $this->equalTo('Sample'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new Handler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->setTemplatePreview('Sample');
  }

  /**
   * @covers \Papaya\Template\Xslt\Handler::removeTemplatePreview
   */
  public function testRemoveTemplatePreview() {
    $session = $this->createMock(\Papaya\Session::class);
    $values = $this->getMockBuilder(\Papaya\Session\Values::class)->disableOriginalConstructor()->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTemplate'), $this->equalTo(NULL));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new Handler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->removeTemplatePreview();
  }


}

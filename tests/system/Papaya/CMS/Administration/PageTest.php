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

namespace Papaya\CMS\Administration {

  use InvalidArgumentException;
  use Papaya\CMS\Administration\UI as AdministrationUI;
  use Papaya\Message\Manager as MessageManager;
  use Papaya\Session;
  use Papaya\Template;
  use Papaya\TestFramework\TestCase;
  use Papaya\UI\Toolbar;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Administration\Page
   */
  class PageTest extends TestCase {

    public function testConstructor() {
      $page = new Page_TestProxy($ui = $this->mockPapaya()->administrationUI());
      $this->assertSame($ui, $page->getUI());
    }

    public function testConstructorWithModuleId() {
      $page = new Page_TestProxy($ui = $this->mockPapaya()->administrationUI(), 'abc123');
      $this->assertSame('abc123', $page->getModuleId());
    }

    public function testConstructorWithTemplateForBC() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = $this->createMock(Template::class);
      $page = new Page_TestProxy($template);
      $this->assertSame($template, $page->getTemplate());
    }

    public function testConstructorWithInvalidArgumentExpectingException() {
      $this->expectException(InvalidArgumentException::class);
      $this->expectExceptionMessage(
        'Argument should be a "Papaya\CMS\Administration\UI" and can be a "Papaya\Template" for old code.'
      );
      /** @noinspection PhpParamsInspection */
      new Page_TestProxy(new \stdClass());
    }

    public function testPageExecuteWithoutParts() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|AdministrationUI $ui */
      $ui = $this->mockPapaya()->administrationUI();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = $ui->template();
      $template
        ->expects($this->never())
        ->method('add');
      $template
        ->expects($this->once())
        ->method('addMenu')
        ->with('');
      $page = new Page_TestProxy($ui);
      $page->papaya($this->mockPapaya()->application());
      $page();
    }

    public function testPageExecuteWithContentPart() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|AdministrationUI $ui */
      $ui = $this->mockPapaya()->administrationUI();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = $ui->template();
      $template
        ->expects($this->once())
        ->method('add')
        ->with(
        /** @lang XML */
          '<foo/>', 'centercol'
        );
      $template
        ->expects($this->once())
        ->method('addMenu');
      $content = $this->createMock(Page\Part::class);
      $content
        ->expects($this->once())
        ->method('getXml')
        ->willReturn(
        /** @lang XML */
          '<foo/>'
        );
      $page = new Page_TestProxy($ui);
      $page->papaya($this->mockPapaya()->application());
      $page->parts()->content = $content;
      $page();
    }

    public function testExecuteWithAccessForbidden() {
      $messages = $this->createMock(MessageManager::class);
      $messages
        ->expects($this->once())
        ->method('displayError')
        ->with('Access forbidden.');

      $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
      $page->papaya($this->mockPapaya()->application(['messages' => $messages]));

      $page->allowAccess = FALSE;
      $page();
    }

    public function testExecuteWithParameterGroupKeepsStatusInSession() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session $session */
      $session = $this->createMock(Session::class);
      $session
        ->expects($this->once())
        ->method('getValue')
        ->with([Page_TestProxy::class, 'parameters', 'demo'])
        ->willReturn(['a-parameter' => 'a-value']);
      $session
        ->expects($this->once())
        ->method('setValue')
        ->with([Page_TestProxy::class, 'parameters', 'demo'], ['a-parameter' => 'a-value']);

      $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
      $page->papaya(
        $this->mockPapaya()->application(['session' => $session])
      );

      $page->_parameterGroup = 'demo';
      $page();
    }

    public function testCreatePartWithUnknownNameExpectingFalse() {
      $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
      $this->assertFalse($page->createPart('NonExistingPart'));
    }

    public function testPartsGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Page\Parts $parts */
      $parts = $this
        ->getMockBuilder(Page\Parts::class)
        ->disableOriginalConstructor()
        ->getMock();
      $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
      $page->parts($parts);
      $this->assertSame($parts, $page->parts());
    }

    public function testToolbarGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Toolbar $toolbar */
      $toolbar = $this->createMock(Toolbar::class);
      $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
      $page->toolbar($toolbar);
      $this->assertSame($toolbar, $page->toolbar());
    }

    public function testToolbarGetImplicitCreate() {
      $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
      $this->assertInstanceOf(Toolbar::class, $page->toolbar());
    }
  }

  class Page_TestProxy extends Page {

    public $allowAccess = TRUE;

    public $_parameterGroup;

    public function validateAccess() {
      return $this->allowAccess && parent::validateAccess();
    }

  }
}

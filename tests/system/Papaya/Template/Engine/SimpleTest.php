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

namespace Papaya\Template\Engine {

  use Papaya\Template\Simple\AST as SimpleTemplateAST;
  use Papaya\Template\Simple\Exception as SimpleTemplateException;
  use Papaya\Template\Simple\Visitor as SimpleTemplateVisitor;
  use Papaya\TestCase;
  use Papaya\XML\Document as XMLDocument;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Engine\Simple
   */
  class SimpleTest extends TestCase {

    public function testTemplateEngineRun() {
      $engine = new Simple();
      $engine->setTemplateString('Hello /*$foo*/ World!');
      $values = new XMLDocument();
      $values->appendElement('values')->appendElement('foo', [], 'Universe');
      $engine->values($values->documentElement);
      $engine->prepare();
      $engine->run();
      $this->assertEquals('Hello Universe!', $engine->getResult());
    }

    public function testPrepare() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|SimpleTemplateVisitor $visitor */
      $visitor = $this->createMock(SimpleTemplateVisitor::class);
      $visitor
        ->expects($this->once())
        ->method('clear');

      $engine = new Simple();
      $engine->visitor($visitor);
      $engine->prepare();
    }

    /**
     * @throws SimpleTemplateException
     */
    public function testRun() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|SimpleTemplateVisitor $visitor */
      $visitor = $this->createMock(SimpleTemplateVisitor::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|SimpleTemplateAST $ast */
      $ast = $this->createMock(SimpleTemplateAST::class);
      $ast
        ->expects($this->once())
        ->method('accept')
        ->with($visitor);

      $engine = new Simple();
      $engine->visitor($visitor);
      $engine->ast($ast);
      $engine->run();
    }

    public function testGetResult() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|SimpleTemplateVisitor $visitor */
      $visitor = $this->createMock(SimpleTemplateVisitor::class);
      $visitor
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('success');

      $engine = new Simple();
      $engine->visitor($visitor);
      $this->assertEquals('success', $engine->getResult());
    }

    public function testSetTemplateString() {
      $engine = new Simple();
      $engine->setTemplateString('div { color: /*$FG_COLOR*/ #FFF; }');
      $this->assertEquals(
        'div { color: /*$FG_COLOR*/ #FFF; }',
        $engine->getTemplate()
      );
      $this->assertEquals(
        FALSE,
        $engine->getTemplateFile()
      );
    }

    public function testSetTemplateFile() {
      $engine = new Simple();
      $engine->setTemplateFile(__DIR__.'/TestData/valid.css');
      $this->assertNotEmpty(
        $engine->getTemplate()
      );
      $this->assertEquals(
        __DIR__.'/TestData/valid.css',
        $engine->getTemplateFile()
      );
    }

    public function testSetTemplateFileWithInvalidFileNameExpectingException() {
      $engine = new Simple();
      $this->expectException(\InvalidArgumentException::class);
      $engine->setTemplateFile('NON_EXISTING_FILENAME.CSS');
    }

    /**
     * @throws SimpleTemplateException
     */
    public function testAstGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|SimpleTemplateAST $ast */
      $ast = $this->createMock(SimpleTemplateAST::class);
      $engine = new Simple();
      $engine->ast($ast);
      $this->assertSame($ast, $engine->ast());
    }

    /**
     * @throws SimpleTemplateException
     */
    public function testAstGetImplicitCreate() {
      $engine = new Simple();
      $this->assertInstanceOf(SimpleTemplateAST::class, $engine->ast());
    }

    public function testVisitorGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|SimpleTemplateVisitor $visitor */
      $visitor = $this->createMock(SimpleTemplateVisitor::class);
      $engine = new Simple();
      $engine->visitor($visitor);
      $this->assertSame($visitor, $engine->visitor());
    }

    public function testVisitorGetImplicitCreate() {
      $engine = new Simple();
      $this->assertInstanceOf(SimpleTemplateVisitor::class, $engine->visitor());
    }

    public function testIntegration() {
      $values = new XMLDocument();
      $values->appendElement('_')->appendElement('who', 'World');
      $engine = new Simple();
      $engine->setTemplateString('Hello /*$who*/ default!');
      $engine->values($values->documentElement);
      $engine->prepare();
      $engine->run();
      $this->assertSame('Hello World!', $engine->getResult());
    }

    public function testIntegrationWithXpath() {
      $values = new XMLDocument();
      $values->appendElement('_')->appendElement('who', 'World');
      $engine = new Simple();
      $engine->setTemplateString('Hello /*$xpath(./who)*/ default!');
      $engine->values($values->documentElement);
      $engine->prepare();
      $engine->run();
      $this->assertSame('Hello World!', $engine->getResult());
    }
  }
}

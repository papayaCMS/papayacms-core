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

namespace Papaya\Template\Engine;
require_once __DIR__.'/../../../../bootstrap.php';

class SimpleTest extends \Papaya\TestCase {

  /**
   * Integration test - block code coverage
   *
   * @covers \stdClass
   */
  public function testTemplateEngineRun() {
    $engine = new Simple();
    $engine->setTemplateString('Hello /*$foo*/ World!');
    $values = new \Papaya\XML\Document();
    $values->appendElement('values')->appendElement('foo', array(), 'Universe');
    $engine->values($values->documentElement);
    $engine->prepare();
    $engine->run();
    $this->assertEquals('Hello Universe!', $engine->getResult());
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::prepare
   */
  public function testPrepare() {
    $visitor = $this->createMock(\Papaya\Template\Simple\Visitor::class);
    $visitor
      ->expects($this->once())
      ->method('clear');

    $engine = new Simple();
    $engine->visitor($visitor);
    $engine->prepare();
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::run
   */
  public function testRun() {
    $visitor = $this->createMock(\Papaya\Template\Simple\Visitor::class);
    $ast = $this->createMock(\Papaya\Template\Simple\AST::class);
    $ast
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);

    $engine = new Simple();
    $engine->visitor($visitor);
    $engine->ast($ast);
    $engine->run();
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::getResult
   */
  public function testGetResult() {
    $visitor = $this->createMock(\Papaya\Template\Simple\Visitor::class);
    $visitor
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));

    $engine = new Simple();
    $engine->visitor($visitor);
    $this->assertEquals('success', $engine->getResult());
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::callbackGetValue
   */
  public function testCallbackGetValueWithName() {
    $values = new \Papaya\XML\Document();
    $values
      ->appendElement('values')
      ->appendElement('page')
      ->appendElement('group')
      ->appendElement('value', array(), 'success');
    $engine = new Simple();
    $engine->values($values->documentElement);
    $this->assertEquals(
      'success', $engine->callbackGetValue(new \stdClass, 'page.group.value')
    );
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::callbackGetValue
   */
  public function testCallbackGetValueWithXpath() {
    $values = new \Papaya\XML\Document();
    $values
      ->appendElement('values')
      ->appendElement('page')
      ->appendElement('group')
      ->appendElement('value', array(), 'success');
    $engine = new Simple();
    $engine->values($values->documentElement);
    $this->assertEquals(
      'success', $engine->callbackGetValue(new \stdClass, 'xpath(page/group/value)')
    );
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::setTemplateString
   */
  public function testSetTemplateString() {
    $engine = new Simple();
    $engine->setTemplateString('div { color: /*$FG_COLOR*/ #FFF; }');
    $this->assertAttributeEquals(
      'div { color: /*$FG_COLOR*/ #FFF; }',
      '_template',
      $engine
    );
    $this->assertAttributeEquals(
      FALSE,
      '_templateFile',
      $engine
    );
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::setTemplateFile
   */
  public function testSetTemplateFile() {
    $engine = new Simple();
    $engine->setTemplateFile(__DIR__.'/TestData/valid.css');
    $this->assertAttributeNotEmpty(
      '_template',
      $engine
    );
    $this->assertAttributeEquals(
      __DIR__.'/TestData/valid.css',
      '_templateFile',
      $engine
    );
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::setTemplateFile
   */
  public function testSetTemplateFileWithInvalidFileNameExpectingException() {
    $engine = new Simple();
    $this->expectException(\InvalidArgumentException::class);
    $engine->setTemplateFile('NONEXISTING_FILENAME.CSS');
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::ast
   */
  public function testAstGetAfterSet() {
    $ast = $this->createMock(\Papaya\Template\Simple\AST::class);
    $engine = new Simple();
    $engine->ast($ast);
    $this->assertSame($ast, $engine->ast());
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::ast
   */
  public function testAstGetImplicitCreate() {
    $engine = new Simple();
    $this->assertInstanceOf(\Papaya\Template\Simple\AST::class, $engine->ast());
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::visitor
   */
  public function testVisitorGetAfterSet() {
    $visitor = $this->createMock(\Papaya\Template\Simple\Visitor::class);
    $engine = new Simple();
    $engine->visitor($visitor);
    $this->assertSame($visitor, $engine->visitor());
  }

  /**
   * @covers \Papaya\Template\Engine\Simple::visitor
   */
  public function testVisitorGetImplicitCreate() {
    $engine = new Simple();
    $this->assertInstanceOf(\Papaya\Template\Simple\Visitor::class, $engine->visitor());
  }
}

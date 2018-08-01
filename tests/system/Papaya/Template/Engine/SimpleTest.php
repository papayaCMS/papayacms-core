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

class PapayaTemplateEngineSimpleTest extends \PapayaTestCase {

  /**
   * Integration test - block code coverage
   *
   * @covers stdClass
   */
  public function testTemplateEngineRun() {
    $engine = new \PapayaTemplateEngineSimple();
    $engine->setTemplateString('Hello /*$foo*/ World!');
    $values = new \Papaya\Xml\Document();
    $values->appendElement('values')->appendElement('foo', array(), 'Universe');
    $engine->values($values->documentElement);
    $engine->prepare();
    $engine->run();
    $this->assertEquals('Hello Universe!', $engine->getResult());
  }

  /**
  * @covers \PapayaTemplateEngineSimple::prepare
   */
  public function testPrepare() {
    $visitor = $this->createMock(\PapayaTemplateSimpleVisitor::class);
    $visitor
      ->expects($this->once())
      ->method('clear');

    $engine = new \PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $engine->prepare();
  }

  /**
  * @covers \PapayaTemplateEngineSimple::run
   */
  public function testRun() {
    $visitor = $this->createMock(\PapayaTemplateSimpleVisitor::class);
    $ast = $this->createMock(\PapayaTemplateSimpleAst::class);
    $ast
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);

    $engine = new \PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $engine->ast($ast);
    $engine->run();
  }

  /**
  * @covers \PapayaTemplateEngineSimple::getResult
   */
  public function testGetResult() {
    $visitor = $this->createMock(\PapayaTemplateSimpleVisitor::class);
    $visitor
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));

    $engine = new \PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $this->assertEquals('success', $engine->getResult());
  }

  /**
   * @covers \PapayaTemplateEngineSimple::callbackGetValue
   */
  public function testCallbackGetValueWithName() {
    $values = new \Papaya\Xml\Document();
    $values
      ->appendElement('values')
      ->appendElement('page')
      ->appendElement('group')
      ->appendElement('value', array(), 'success');
    $engine = new \PapayaTemplateEngineSimple();
    $engine->values($values->documentElement);
    $this->assertEquals(
      'success', $engine->callbackGetValue(new stdClass, 'page.group.value')
    );
  }

  /**
   * @covers \PapayaTemplateEngineSimple::callbackGetValue
   */
  public function testCallbackGetValueWithXpath() {
    $values = new \Papaya\Xml\Document();
    $values
      ->appendElement('values')
      ->appendElement('page')
      ->appendElement('group')
      ->appendElement('value', array(), 'success');
    $engine = new \PapayaTemplateEngineSimple();
    $engine->values($values->documentElement);
    $this->assertEquals(
      'success', $engine->callbackGetValue(new stdClass, 'xpath(page/group/value)')
    );
  }

  /**
  * @covers \PapayaTemplateEngineSimple::setTemplateString
  */
  public function testSetTemplateString() {
    $engine = new \PapayaTemplateEngineSimple();
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
  * @covers \PapayaTemplateEngineSimple::setTemplateFile
  */
  public function testSetTemplateFile() {
    $engine = new \PapayaTemplateEngineSimple();
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
  * @covers \PapayaTemplateEngineSimple::setTemplateFile
  */
  public function testSetTemplateFileWithInvalidFileNameExpectingException() {
    $engine = new \PapayaTemplateEngineSimple();
    $this->expectException(InvalidArgumentException::class);
    $engine->setTemplateFile('NONEXISTING_FILENAME.CSS');
  }

  /**
  * @covers \PapayaTemplateEngineSimple::ast
  */
  public function testAstGetAfterSet() {
    $ast = $this->createMock(\PapayaTemplateSimpleAst::class);
    $engine = new \PapayaTemplateEngineSimple();
    $engine->ast($ast);
    $this->assertSame($ast, $engine->ast());
  }

  /**
  * @covers \PapayaTemplateEngineSimple::ast
  */
  public function testAstGetImplicitCreate() {
    $engine = new \PapayaTemplateEngineSimple();
    $this->assertInstanceOf(\PapayaTemplateSimpleAst::class, $engine->ast());
  }

  /**
  * @covers \PapayaTemplateEngineSimple::visitor
  */
  public function testVisitorGetAfterSet() {
    $visitor = $this->createMock(\PapayaTemplateSimpleVisitor::class);
    $engine = new \PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $this->assertSame($visitor, $engine->visitor());
  }

  /**
  * @covers \PapayaTemplateEngineSimple::visitor
  */
  public function testVisitorGetImplicitCreate() {
    $engine = new \PapayaTemplateEngineSimple();
    $this->assertInstanceOf(\PapayaTemplateSimpleVisitor::class, $engine->visitor());
  }
}

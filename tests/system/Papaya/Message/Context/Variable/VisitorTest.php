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

class PapayaMessageContextVariableVisitorTest extends \PapayaTestCase {

  /**
  * @covers \PapayaMessageContextVariableVisitor::__construct
  */
  public function testConstructor() {
    $visitor = new \PapayaMessageContextVariableVisitorProxy(21, 42);
    $this->assertAttributeEquals(
      21,
      '_depth',
      $visitor
    );
    $this->assertAttributeEquals(
      42,
      '_stringLength',
      $visitor
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::__toString
  */
  public function testMagicMethodToString() {
    $visitor = new \PapayaMessageContextVariableVisitorProxy(21, 42);
    $this->assertEquals(
      'variable dump',
      (string)$visitor
    );
  }

  /**
   * @covers \PapayaMessageContextVariableVisitor::visitVariable
   * @dataProvider dataProviderForVisitVariable
   * @param string $expected
   * @param mixed $with
   */
  public function testVisitVariable($expected, $with) {
    $visitor = new \PapayaMessageContextVariableVisitorProxy(21, 42);
    $visitor->visitVariable($with);
    $this->assertAttributeEquals(
      $expected,
      '_visitedVariableType',
      $visitor
    );
  }

  public static function dataProviderForVisitVariable() {
    return array(
      array('array', array()),
      array('boolean', TRUE),
      array('integer', 42),
      array('float', 42.21),
      array('null', NULL),
      array('object', new stdClass()),
      array('resource', fopen('php://memory', 'rwb')),
      array('string', ''),
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_pushObjectStack
  */
  public function testPushObjectStack() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->assertAttributeEquals(
      array('hash1', 'hash2'),
      '_objectStack',
      $visitor
    );
    $this->assertAttributeEquals(
      array('hash1' => 1, 'hash2' => 2),
      '_objectList',
      $visitor
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_popObjectStack
  */
  public function testPopObjectStack() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $visitor->_popObjectStack('hash2');
    $this->assertAttributeEquals(
      array('hash1'),
      '_objectStack',
      $visitor
    );
    $this->assertAttributeEquals(
      array('hash1' => 1, 'hash2' => 2),
      '_objectList',
      $visitor
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_popObjectStack
  */
  public function testPopObjectStackExpectingException() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->expectException(LogicException::class);
    $visitor->_popObjectStack('hash1');
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_isObjectRecursion
  */
  public function testIsObjectRecursionExpectingTrue() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->assertTrue(
      $visitor->_isObjectRecursion('hash1')
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_isObjectRecursion
  */
  public function testIsObjectRecursionExpectingFalse() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->assertFalse(
      $visitor->_isObjectRecursion('hash3')
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_isObjectDuplicate
  */
  public function testIsObjectDuplicateExpectingTrue() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->assertTrue(
      $visitor->_isObjectDuplicate('hash1')
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_isObjectDuplicate
  */
  public function testIsObjectDuplicateExpectingFalse() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->assertFalse(
      $visitor->_isObjectDuplicate('hash3')
    );
  }

  /**
  * @covers \PapayaMessageContextVariableVisitor::_getObjectIndex
  */
  public function testGetObjectIndex() {
    $visitor = $this->getVisitorFixtureForObjectTest();
    $this->assertEquals(
      2,
      $visitor->_getObjectIndex('hash2')
    );
  }

  public function getVisitorFixtureForObjectTest() {
    $visitor = new \PapayaMessageContextVariableVisitorProxy(21, 42);
    $visitor->_pushObjectStack('hash1');
    $visitor->_pushObjectStack('hash2');
    return $visitor;
  }
}

class PapayaMessageContextVariableVisitorProxy extends \PapayaMessageContextVariableVisitor {

  private $_visitedVariableType = '';

  public function get() {
    return 'variable dump';
  }

  public function visitArray(array $array) {
    $this->_visitedVariableType = 'array';
  }

  public function visitBoolean($boolean) {
    $this->_visitedVariableType = 'boolean';
  }

  public function visitInteger($integer) {
    $this->_visitedVariableType = 'integer';
  }

  public function visitFloat($float) {
    $this->_visitedVariableType = 'float';
  }

  public function visitNull($null) {
    $this->_visitedVariableType = 'null';
  }

  public function visitObject($object) {
    $this->_visitedVariableType = 'object';
  }

  public function visitResource($resource) {
    $this->_visitedVariableType = 'resource';
  }

  public function visitString($string) {
    $this->_visitedVariableType = 'string';
  }

  public function _pushObjectStack($hash) {
    parent::_pushObjectStack($hash);
  }

  public function _popObjectStack($hash) {
    parent::_popObjectStack($hash);
  }

  public function _isObjectRecursion($hash) {
    return parent::_isObjectRecursion($hash);
  }

  public function _isObjectDuplicate($hash) {
    return parent::_isObjectDuplicate($hash);
  }
  public function _getObjectIndex($hash) {
    return parent::_getObjectIndex($hash);
  }
}

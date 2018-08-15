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

namespace Papaya\Content\Structure {

  require_once __DIR__.'/../../../../bootstrap.php';

  class NodeTest extends \PapayaTestCase {

    /**
     * @covers Node
     */
    public function testIssetWithValidPropertyExpectingTrue() {
      $node = new Node_TestProxy();
      $this->assertTrue(isset($node->name));
    }

    /**
     * @covers Node
     */
    public function testIssetWithInvalidPropertyExpectingFalse() {
      $node = new Node_TestProxy();
      $this->assertFalse(isset($node->INVALID));
    }


    /**
     * @covers       Node
     * @dataProvider providePropertyValues
     * @param mixed $expected
     * @param string $name
     * @param mixed $value
     */
    public function testGetAfterSet($expected, $name, $value) {
      $node = new Node_TestProxy();
      $node->$name = $value;
      $this->assertEquals($expected, $node->$name);
    }

    /**
     * @covers Node
     */
    public function testSetInvalidPropertyExpectingException() {
      $node = new Node_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $node->INVALID = 'foo';
    }

    /**
     * @covers Node
     */
    public function testGetInvalidPropertyExpectingException() {
      $node = new Node_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $node->INVALID;
    }

    /**
     * @covers Node
     */
    public function testSetInvalidPropertyNameExpectingException() {
      $node = new Node_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $node->name = ':';
    }

    public static function providePropertyValues() {
      return array(
        array('success', 'name', 'success'),
        array('success', 'getter', ''),
        array('success', 'setter', 'success'),
        array('success', 'property', 'success')
      );
    }
  }

  class Node_TestProxy extends Node {

    public function __construct() {
      parent::__construct(
        array(
          'name' => 'test',
          'getter' => '',
          'setter' => '',
          'property' => ''
        )
      );
    }

    public function getGetter() {
      return 'success';
    }

    public function setSetter($value = 'success') {
      $this->setValue('setter', $value);
    }

  }
}

<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\BaseObject {

  use Papaya\Test\TestCase;
  use UnexpectedValueException;

  /**
   * @covers \Papaya\BaseObject\DeclaredProperties
   */
  class DeclaredPropertiesTest extends TestCase {

    public function testReadWriteProperty() {
      $object = new DeclaredProperties_TestProxy();
      $this->assertFalse(isset($object->field));
      $object->field = 'success';
      $this->assertTrue(isset($object->field));
      $this->assertSame('success', $object->field);
      unset($object->field);
      $this->assertFalse(isset($object->field));
    }

    public function testReadWritePropertyWithMethods() {
      $object = new DeclaredProperties_TestProxy();
      $this->assertFalse(isset($object->property));
      $object->property = 'success';
      $this->assertTrue(isset($object->property));
      $this->assertSame('success', $object->property);
      unset($object->property);
      $this->assertFalse(isset($object->property));
    }

    public function testReadWritePropertyWithClosures() {
      $object = new DeclaredProperties_TestProxy();
      $this->assertFalse(isset($object->closure));
      $object->closure = 'success';
      $this->assertTrue(isset($object->closure));
      $this->assertSame('success', $object->closure);
      unset($object->closure);
      $this->assertFalse(isset($object->closure));
    }

    public function testReadWriteReadOnlyProperty() {
      $object = new DeclaredProperties_TestProxy();
      $this->assertFalse(isset($object->readOnly));
      $object->property = 'success';
      $this->assertTrue(isset($object->readOnly));
      $this->assertSame('success', $object->readOnly);
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage(
        'Can not write readonly property "Papaya\BaseObject\DeclaredProperties_TestProxy::$readOnly".'
      );
      $object->readOnly = 'fail';
    }

    public function testReadUnknownProperty() {
      $object = new DeclaredProperties_TestProxy();
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage(
        'Can not read unknown property "Papaya\BaseObject\DeclaredProperties_TestProxy::$nonExisting".'
      );
      $object->nonExisting;
    }

    public function testWriteUnknownProperty() {
      $object = new DeclaredProperties_TestProxy();
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage(
        'Can not write unknown property "Papaya\BaseObject\DeclaredProperties_TestProxy::$nonExisting".'
      );
      $object->nonExisting = 'fail';
    }

    public function testReadInvalidProperty() {
      $object = new DeclaredProperties_TestProxy();
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage(
        'Invalid declaration: Can not read property "Papaya\BaseObject\DeclaredProperties_TestProxy::$invalid".'
      );
      $object->invalid;
    }

    public function testWriteInvalidProperty() {
      $object = new DeclaredProperties_TestProxy();
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage(
        'Invalid declaration: Can not write property "Papaya\BaseObject\DeclaredProperties_TestProxy::$invalid".'
      );
      $object->invalid = 'fail';
    }

  }

  /**
   * @property mixed $field
   * @property mixed $property
   * @property mixed $closure
   * @property mixed $readOnly
   * @property mixed $invalid
   */
  class DeclaredProperties_TestProxy implements Interfaces\Properties {

    use DeclaredProperties;

    private $_field;

    public function getPropertyDeclaration() {
      return [
        'field' => ['_field', '_field'],
        'property' => ['getField', 'setField'],
        'closure' => [function() { return $this->getField(); }, function($value) { $this->setField($value); }],
        'readOnly' => ['_field'],
        'invalid' => ['_nonExisting', '_nonExisting']
      ];
    }

    public function getField() {
      return $this->_field;
    }

    public function setField($value) {
      $this->_field = $value;
    }
  }
}

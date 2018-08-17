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

namespace Papaya\UI\Control {

  require_once __DIR__.'/../../../../bootstrap.php';

  class PartTest extends \Papaya\TestCase {


    /**
     * @covers \Papaya\UI\Control\Part::__get
     * @covers \Papaya\UI\Control\Part::__set
     */
    public function testPropertyGetAfterSetByName() {
      $control = new Part_TestProxy();
      $control->propertyOne = 'success';
      $this->assertEquals('success', $control->propertyOne);
    }

    /**
     * @covers \Papaya\UI\Control\Part::__get
     * @covers \Papaya\UI\Control\Part::__set
     */
    public function testPropertyGetAfterSetByMethods() {
      $control = new Part_TestProxy();
      $control->propertyTwo = 'success';
      $this->assertEquals('success', $control->propertyTwo);
    }

    /**
     * @covers \Papaya\UI\Control\Part::__get
     */
    public function testPropertyGetUnknownExpectingException() {
      $control = new Part_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not read unknown property "Papaya\UI\Control\Part_TestProxy::$propertyUnknown".');
      /** @noinspection PhpUndefinedFieldInspection */
      $control->propertyUnknown;
    }

    /**
     * @covers \Papaya\UI\Control\Part::__get
     */
    public function testPropertyGetInvalidExpectingException() {
      $control = new Part_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Invalid declaration: Can not read property "Papaya\UI\Control\Part_TestProxy::$propertyFour".');
      $control->propertyFour;
    }

    /**
     * @covers \Papaya\UI\Control\Part::__set
     */
    public function testPropertyReadOnlyExpectingException() {
      $control = new Part_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage(
        'Invalid declaration: Can not write readonly property "Papaya\UI\Control\Part_TestProxy::$propertyThree".'
      );
      /** @noinspection Annotator */
      $control->propertyThree = 'fail';
    }

    /**
     * @covers \Papaya\UI\Control\Part::__set
     */
    public function testPropertySetUnknownExpectingException() {
      $control = new Part_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not write unknown property "Papaya\UI\Control\Part_TestProxy::$propertyUnknown".');
      /** @noinspection PhpUndefinedFieldInspection */
      $control->propertyUnknown = 'success';
    }

    /**
     * @covers \Papaya\UI\Control\Part::__set
     */
    public function testPropertySetInvalidExpectingException() {
      $control = new Part_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage(' Can not write property "Papaya\UI\Control\Part_TestProxy::$propertyFour".');
      $control->propertyFour = 'fail';
    }
  }

  /**
   * @property string $propertyOne
   * @property string $propertyTwo
   * @property-read string $propertyThree
   * @property string $propertyFour
   */
  class Part_TestProxy extends Part {

    protected $_property;

    protected $_declaredProperties = array(
      'propertyOne' => array('_property', '_property'),
      'propertyTwo' => array('getProperty', 'setProperty'),
      'propertyThree' => array('_property'),
      'propertyFour' => array('_invalid', '_invalid')
    );

    public $nodeStub = array();

    public function getProperty() {
      return $this->_property;
    }

    public function setProperty($value) {
      $this->_property = $value;
    }

    public function appendTo(\Papaya\XML\Element $parent) {
    }
  }
}

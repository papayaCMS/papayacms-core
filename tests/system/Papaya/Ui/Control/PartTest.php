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

class PapayaUiControlPartTest extends \PapayaTestCase {


  /**
  * @covers \Papaya\UI\Control\Part::__get
  * @covers \Papaya\UI\Control\Part::__set
  */
  public function testPropertyGetAfterSetByName() {
    $control = new \PapayaUiControlPart_TestProxy();
    $control->propertyOne = 'success';
    $this->assertEquals('success', $control->propertyOne);
  }

  /**
  * @covers \Papaya\UI\Control\Part::__get
  * @covers \Papaya\UI\Control\Part::__set
  */
  public function testPropertyGetAfterSetByMethods() {
    $control = new \PapayaUiControlPart_TestProxy();
    $control->propertyTwo = 'success';
    $this->assertEquals('success', $control->propertyTwo);
  }

  /**
  * @covers \Papaya\UI\Control\Part::__get
  */
  public function testPropertyGetUnknownExpectingException() {
    $control = new \PapayaUiControlPart_TestProxy();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not read unknown property "PapayaUiControlPart_TestProxy::$propertyUnknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $control->propertyUnknown;
  }

  /**
  * @covers \Papaya\UI\Control\Part::__get
  */
  public function testPropertyGetInvalidExpectingException() {
    $control = new \PapayaUiControlPart_TestProxy();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid declaration: Can not read property "PapayaUiControlPart_TestProxy::$propertyFour".');
    $control->propertyFour;
  }

  /**
  * @covers \Papaya\UI\Control\Part::__set
  */
  public function testPropertyReadOnlyExpectingException() {
    $control = new \PapayaUiControlPart_TestProxy();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage(
      'Invalid declaration: Can not write readonly property "PapayaUiControlPart_TestProxy::$propertyThree".'
    );
    /** @noinspection Annotator */
    $control->propertyThree = 'fail';
  }

  /**
  * @covers \Papaya\UI\Control\Part::__set
  */
  public function testPropertySetUnknownExpectingException() {
    $control = new \PapayaUiControlPart_TestProxy();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not write unknown property "PapayaUiControlPart_TestProxy::$propertyUnknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $control->propertyUnknown = 'success';
  }

  /**
  * @covers \Papaya\UI\Control\Part::__set
  */
  public function testPropertySetInvalidExpectingException() {
    $control = new \PapayaUiControlPart_TestProxy();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage(' Can not write property "PapayaUiControlPart_TestProxy::$propertyFour".');
    $control->propertyFour = 'fail';
  }
}

/**
* @property string $propertyOne
* @property string $propertyTwo
* @property-read string $propertyThree
* @property string $propertyFour
*/
class PapayaUiControlPart_TestProxy extends \Papaya\UI\Control\Part {

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

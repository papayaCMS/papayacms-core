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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryOptionsTest extends \PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__construct
   */
  public function testConstructor() {
    $options = new \PapayaUiDialogFieldFactoryOptions(array('name' => 'success'));
    $this->assertEquals('success', $options->name);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::assign
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   */
  public function testAssign() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->assign(array('name' => 'success', 'unknown_option_name' => 'ignored'));
    $this->assertEquals('success', $options->name);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__isset
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   */
  public function testMagicMethodIssetExpectingTrue() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->assertTrue(isset($options->name));
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__isset
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   */
  public function testMagicMethodIssetExpectingFalse() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->assertFalse(isset($options->unknownOptionName));
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__get
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   */
  public function testMagicMethodGetReturningDefaultValue() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->assertEquals('', $options->name);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__get
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   */
  public function testMagicMethodGetWithUnknownOptionExpectingException() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->expectException(\PapayaUiDialogFieldFactoryExceptionInvalidOption::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $options->invalidOptionName;
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__get
   * @covers \PapayaUiDialogFieldFactoryOptions::__set
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   */
  public function testMagicMethodGetAfterSet() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->name = 'success';
    $this->assertEquals('success', $options->name);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::__unset
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   */
  public function testMagicMethodUnset() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->name = 'success';
    unset($options->name);
    $this->assertEquals('', $options->name);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::offsetExists
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   */
  public function testOffsetExistsExpectingTrue() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->assertTrue(isset($options['name']));
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::offsetExists
   * @covers \PapayaUiDialogFieldFactoryOptions::exists
   */
  public function testOffsetExistsExpectingFalse() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->assertFalse(isset($options['unknown_option_name']));
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::offsetGet
   * @covers \PapayaUiDialogFieldFactoryOptions::offsetSet
   */
  public function testOffsetGetAfterSet() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options['name'] = 'success';
    $this->assertEquals('success', $options['name']);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::offsetUnset
   */
  public function testOffsetUnset() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $option['name'] = 'success';
    unset($options['name']);
    $this->assertEquals('', $options['name']);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @dataProvider provideOptionData
   * @param mixed $expected
   * @param string $name
   * @param mixed $value
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function testGetSetOptions($expected, $name, $value) {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->$name = $value;
    $this->assertSame(
      $expected, $options->$name
    );
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithFilter() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->validation = $filter = $this->createMock(\Papaya\Filter::class);
    $this->assertSame($filter, $options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithEmtpyValidation() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->validation = '';
    $this->assertNull($options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithEmtpyValidationButMandatory() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->validation = '';
    $options->mandatory = TRUE;
    $this->assertInstanceOf(\PapayaFilterNotEmpty::class, $options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithArray() {
    $getFilterCallback = function() {};
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('generator', FALSE, $getFilterCallback)
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->filterFactory($factory);
    $options->validation = $getFilterCallback;
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithClass() {
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('generator', FALSE, array(\PapayaFilterNotEmpty::class))
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->filterFactory($factory);
    $options->validation = \PapayaFilterNotEmpty::class;
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithRegex() {
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('regex', FALSE, '(sample)')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->filterFactory($factory);
    $options->validation = '(sample)';
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::get
   * @covers \PapayaUiDialogFieldFactoryOptions::set
   * @covers \PapayaUiDialogFieldFactoryOptions::getValidation
   */
  public function testGetValidationWithNamedFilterProfile() {
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('isSomething', FALSE)
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->filterFactory($factory);
    $options->validation = 'isSomething';
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::filterFactory
   */
  public function testFilterFactoryGetAfterSet() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $options->filterFactory($factory = $this->createMock(\Papaya\Filter\Factory::class));
    $this->assertSame($factory, $options->filterFactory());
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryOptions::filterFactory
   */
  public function testFilterFactoryGetImplicitCreate() {
    $options = new \PapayaUiDialogFieldFactoryOptions();
    $this->assertInstanceOf(\Papaya\Filter\Factory::class, $options->filterFactory());
  }

  public static function provideOptionData() {
    $sampleObject = new stdClass();
    return array(
      array('value', 'name', 'value'),
      array('value', 'caption', 'value'),
      array('value', 'hint', 'value'),
      array('value', 'default', 'value'),
      array(TRUE, 'mandatory', TRUE),
      array(FALSE, 'mandatory', FALSE),
      array('value', 'parameters', 'value'),
      array($sampleObject, 'context', $sampleObject),
    );
  }
}

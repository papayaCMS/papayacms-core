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

namespace Papaya\UI\Dialog\Field\Factory;
require_once __DIR__.'/../../../../../../bootstrap.php';

class OptionsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__construct
   */
  public function testConstructor() {
    $options = new Options(array('name' => 'success'));
    $this->assertEquals('success', $options->name);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::assign
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   */
  public function testAssign() {
    $options = new Options();
    $options->assign(array('name' => 'success', 'unknown_option_name' => 'ignored'));
    $this->assertEquals('success', $options->name);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__isset
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   */
  public function testMagicMethodIssetExpectingTrue() {
    $options = new Options();
    $this->assertTrue(isset($options->name));
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__isset
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   */
  public function testMagicMethodIssetExpectingFalse() {
    $options = new Options();
    $this->assertFalse(isset($options->unknownOptionName));
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   */
  public function testMagicMethodGetReturningDefaultValue() {
    $options = new Options();
    $this->assertEquals('', $options->name);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   */
  public function testMagicMethodGetWithUnknownOptionExpectingException() {
    $options = new Options();
    $this->expectException(\Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $options->invalidOptionName;
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   */
  public function testMagicMethodGetAfterSet() {
    $options = new Options();
    $options->name = 'success';
    $this->assertEquals('success', $options->name);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::__unset
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   */
  public function testMagicMethodUnset() {
    $options = new Options();
    $options->name = 'success';
    unset($options->name);
    $this->assertEquals('', $options->name);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::offsetExists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   */
  public function testOffsetExistsExpectingTrue() {
    $options = new Options();
    $this->assertTrue(isset($options['name']));
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::offsetExists
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::exists
   */
  public function testOffsetExistsExpectingFalse() {
    $options = new Options();
    $this->assertFalse(isset($options['unknown_option_name']));
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::offsetGet
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::offsetSet
   */
  public function testOffsetGetAfterSet() {
    $options = new Options();
    $options['name'] = 'success';
    $this->assertEquals('success', $options['name']);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::offsetUnset
   */
  public function testOffsetUnset() {
    $options = new Options();
    $option['name'] = 'success';
    unset($options['name']);
    $this->assertEquals('', $options['name']);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @dataProvider provideOptionData
   * @param mixed $expected
   * @param string $name
   * @param mixed $value
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function testGetSetOptions($expected, $name, $value) {
    $options = new Options();
    $options->$name = $value;
    $this->assertSame(
      $expected, $options->$name
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithFilter() {
    $options = new Options();
    $options->validation = $filter = $this->createMock(\Papaya\Filter::class);
    $this->assertSame($filter, $options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithEmtpyValidation() {
    $options = new Options();
    $options->validation = '';
    $this->assertNull($options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithEmtpyValidationButMandatory() {
    $options = new Options();
    $options->validation = '';
    $options->mandatory = TRUE;
    $this->assertInstanceOf(\Papaya\Filter\NotEmpty::class, $options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithArray() {
    $getFilterCallback = function () {
    };
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('generator', FALSE, $getFilterCallback)
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new Options();
    $options->filterFactory($factory);
    $options->validation = $getFilterCallback;
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithClass() {
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('generator', FALSE, array(\Papaya\Filter\NotEmpty::class))
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new Options();
    $options->filterFactory($factory);
    $options->validation = \Papaya\Filter\NotEmpty::class;
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithRegex() {
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('regex', FALSE, '(sample)')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new Options();
    $options->filterFactory($factory);
    $options->validation = '(sample)';
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::get
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::set
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::getValidation
   */
  public function testGetValidationWithNamedFilterProfile() {
    $factory = $this->createMock(\Papaya\Filter\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getFilter')
      ->with('isSomething', FALSE)
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));

    $options = new Options();
    $options->filterFactory($factory);
    $options->validation = 'isSomething';
    $this->assertInstanceOf(\Papaya\Filter::class, $options->validation);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::filterFactory
   */
  public function testFilterFactoryGetAfterSet() {
    $options = new Options();
    $options->filterFactory($factory = $this->createMock(\Papaya\Filter\Factory::class));
    $this->assertSame($factory, $options->filterFactory());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Options::filterFactory
   */
  public function testFilterFactoryGetImplicitCreate() {
    $options = new Options();
    $this->assertInstanceOf(\Papaya\Filter\Factory::class, $options->filterFactory());
  }

  public static function provideOptionData() {
    $sampleObject = new \stdClass();
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

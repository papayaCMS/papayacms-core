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

namespace Papaya\UI\Dialog\Field\Builder;
require_once __DIR__.'/../../../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\Dialog\Field\Builder\FromArray
 */
class FromArrayTest extends \Papaya\TestCase {

  public function testGetFieldsCreateField() {
    $expectedOptions = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => '',
        'mandatory' => TRUE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => FALSE,
        'context' => $owner = new \stdClass
      )
    );
    $fieldFactory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));
    $editFields = array(
      'field' => array('Field caption', '', TRUE, 'input', 42)
    );
    $builder = new FromArray(new \stdClass, $editFields);
    $builder->fieldFactory($fieldFactory);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    $this->assertInstanceOf(
      \Papaya\UI\Dialog\Field::class, $fields[0]
    );
  }

  public function testGetFieldsCreateFieldMappingFilter() {
    $expectedOptions = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => 'isCssColor',
        'mandatory' => TRUE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => FALSE,
        'context' => $owner = new \stdClass
      )
    );
    $fieldFactory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));
    $editFields = array(
      'field' => array('Field caption', 'isHtmlColor', TRUE, 'input', 42)
    );
    $builder = new FromArray(new \stdClass, $editFields);
    $builder->fieldFactory($fieldFactory);
    $builder->getFields();
  }

  public function testGetFieldsCreateFieldWithDisabledField() {
    $expectedOptions = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => '',
        'mandatory' => FALSE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => TRUE,
        'context' => $owner = new \stdClass
      )
    );
    $fieldFactory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));
    $editFields = array(
      'field' => array('Field caption', '', FALSE, 'disabled_input', 42)
    );
    $builder = new FromArray($owner, $editFields);
    $builder->fieldFactory($fieldFactory);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    $this->assertInstanceOf(\Papaya\UI\Dialog\Field::class, $fields[0]);
  }

  public function testGetFieldsCreatesGroupWithOneField() {
    $expectedOptions = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => '',
        'mandatory' => FALSE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => FALSE,
        'context' => $owner = new \stdClass
      )
    );
    $fieldFactory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));
    $editFields = array(
      'Group caption',
      'field' => array('Field caption', '', FALSE, 'input', 42)
    );
    $builder = new FromArray(new \stdClass, $editFields);
    $builder->fieldFactory($fieldFactory);
    $fields = $builder->getFields();
    $this->assertInstanceOf(
      \Papaya\UI\Dialog\Field\Group::class, $fields[0]
    );
    $this->assertCount(1, $fields[0]->fields);
  }

  public function testCreatePhraseTroughAddGroupExpectingString() {
    $editFields = array(
      'Group caption',
    );
    $builder = new FromArray(new \stdClass, $editFields);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    /** @var \Papaya\UI\Dialog\Field\Group $field */
    $field = $fields[0];
    $this->assertEquals(
      'Group caption', $field->getCaption()
    );
  }

  public function testCreatePhraseTroughAddGroupExpectingObject() {
    $editFields = array(
      'Group caption',
    );
    $builder = new FromArray(new \stdClass, $editFields, TRUE);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    $this->assertSame(
      'Group caption', $fields[0]->caption
    );
  }

  public function testFieldFactoryGetAfterSet() {
    $builder = new FromArray(new \stdClass, array(), TRUE);
    $builder->fieldFactory($factory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class));
    $this->assertSame($factory, $builder->fieldFactory());
  }

  public function testFieldFactoryGetImplicitCreate() {
    $builder = new FromArray(new \stdClass, array(), TRUE);
    $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Factory::class, $builder->fieldFactory());
  }
}

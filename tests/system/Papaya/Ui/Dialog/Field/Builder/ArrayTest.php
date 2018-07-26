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

class PapayaUiDialogFieldBuilderArrayTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::__construct
  */
  public function testConstructor() {
    $editFields = array('Success');
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, $editFields, FALSE);
    $this->assertAttributeEquals(
      $editFields, '_editFields', $builder
    );
    $this->assertAttributeSame(
      FALSE, '_translatePhrases', $builder
    );
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::getFields
  * @covers \PapayaUiDialogFieldBuilderArray::_addField
  */
  public function testGetFieldsCreateField() {
    $expectedOptions = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => '',
        'mandatory' => TRUE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => FALSE,
        'context' => $owner = new stdClass
      )
    );
    $fieldFactory = $this->createMock(PapayaUiDialogFieldFactory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $editFields = array(
      'field' => array('Field caption', '', TRUE, 'input', 42)
    );
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, $editFields);
    $builder->fieldFactory($fieldFactory);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    $this->assertInstanceOf(
      PapayaUiDialogField::class, $fields[0]
    );
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::getFields
  * @covers \PapayaUiDialogFieldBuilderArray::_addField
  */
  public function testGetFieldsCreateFieldMappingFilter() {
    $expectedOptions = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => 'isCssColor',
        'mandatory' => TRUE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => FALSE,
        'context' => $owner = new stdClass
      )
    );
    $fieldFactory = $this->createMock(PapayaUiDialogFieldFactory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $editFields = array(
      'field' => array('Field caption', 'isHtmlColor', TRUE, 'input', 42)
    );
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, $editFields);
    $builder->fieldFactory($fieldFactory);
    $builder->getFields();
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::getFields
  * @covers \PapayaUiDialogFieldBuilderArray::_addField
  */
  public function testGetFieldsCreateFieldWithDisabledField() {
    $expectedOptions = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => '',
        'mandatory' => FALSE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => TRUE,
        'context' => $owner = new stdClass
      )
    );
    $fieldFactory = $this->createMock(PapayaUiDialogFieldFactory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $editFields = array(
      'field' => array('Field caption', '', FALSE, 'disabled_input', 42)
    );
    $builder = new \PapayaUiDialogFieldBuilderArray($owner, $editFields);
    $builder->fieldFactory($fieldFactory);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    $this->assertInstanceOf(PapayaUiDialogField::class, $fields[0]);
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::getFields
  * @covers \PapayaUiDialogFieldBuilderArray::_addGroup
  * @covers \PapayaUiDialogFieldBuilderArray::_addField
  */
  public function testGetFieldsCreatesGroupWithOneField() {
    $expectedOptions = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'field',
        'caption' => 'Field caption',
        'validation' => '',
        'mandatory' => FALSE,
        'parameters' => 42,
        'hint' => '',
        'default' => NULL,
        'disabled' => FALSE,
        'context' => $owner = new stdClass
      )
    );
    $fieldFactory = $this->createMock(PapayaUiDialogFieldFactory::class);
    $fieldFactory
      ->expects($this->once())
      ->method('getField')
      ->with('input', $expectedOptions)
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $editFields = array(
      'Group caption',
      'field' => array('Field caption', '', FALSE, 'input', 42)
    );
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, $editFields);
    $builder->fieldFactory($fieldFactory);
    $fields = $builder->getFields();
    $this->assertInstanceOf(
      PapayaUiDialogFieldGroup::class, $fields[0]
    );
    $this->assertCount(1, $fields[0]->fields);
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::getFields
  * @covers \PapayaUiDialogFieldBuilderArray::_createPhrase
  */
  public function testCreatePhraseTroughAddGroupExpectingString() {
    $editFields = array(
      'Group caption',
    );
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, $editFields);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    /** @var \PapayaUiDialogFieldGroup $field */
    $field = $fields[0];
    $this->assertEquals(
      'Group caption', $field->getCaption()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::getFields
  * @covers \PapayaUiDialogFieldBuilderArray::_createPhrase
  */
  public function testCreatePhraseTroughAddGroupExpectingObject() {
    $editFields = array(
      'Group caption',
    );
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, $editFields, TRUE);
    $fields = $builder->getFields();
    $this->assertCount(1, $fields);
    $this->assertAttributeInstanceOf(
      PapayaUiStringTranslated::class, '_caption', $fields[0]
    );
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::fieldFactory
  */
  public function testFieldFactoryGetAfterSet() {
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, array(), TRUE);
    $builder->fieldFactory($factory = $this->createMock(PapayaUiDialogFieldFactory::class));
    $this->assertSame($factory, $builder->fieldFactory());
  }

  /**
  * @covers \PapayaUiDialogFieldBuilderArray::fieldFactory
  */
  public function testFieldFactoryGetImplicitCreate() {
    $builder = new \PapayaUiDialogFieldBuilderArray(new stdClass, array(), TRUE);
    $this->assertInstanceOf(PapayaUiDialogFieldFactory::class, $builder->fieldFactory());
  }
}

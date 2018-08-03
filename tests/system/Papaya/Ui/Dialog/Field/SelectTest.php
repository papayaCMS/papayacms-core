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

class PapayaUiDialogFieldSelectTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::__construct
  * @covers \Papaya\UI\Dialog\Field\Select::setValues
  * @covers \Papaya\UI\Dialog\Field\Select::setValueMode
  * @covers \Papaya\UI\Dialog\Field\Select::_createFilter
  */
  public function testConstructor() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array(21 => 'half', 42 => 'full')
    );
    $this->assertEquals('Caption', $select->getCaption());
    $this->assertEquals('name', $select->getName());
    $this->assertAttributeEquals(
      array(21 => 'half', 42 => 'full'), '_values', $select
    );
    $this->assertEquals(
      new \Papaya\Filter\ArrayKey(array(21 => 'half', 42 => 'full')), $select->getFilter()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::__construct
  * @covers \Papaya\UI\Dialog\Field\Select::setValues
  * @covers \Papaya\UI\Dialog\Field\Select::setValueMode
  * @covers \Papaya\UI\Dialog\Field\Select::_createFilter
  */
  public function testConstructorWithTraversable() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', $iterator = new ArrayIterator(array(21 => 'half', 42 => 'full'))
    );
    $this->assertAttributeSame(
      $iterator, '_values', $select
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::setValues
  * @covers \Papaya\UI\Dialog\Field\Select::getValues
  */
  public function testGetValuesAfterSetValues() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array()
    );
    $select->setValues(array(21 => 'half', 42 => 'full'));
    $this->assertEquals(array(21 => 'half', 42 => 'full'), $select->getValues());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::setValueMode
  * @covers \Papaya\UI\Dialog\Field\Select::getValueMode
  */
  public function testGetValueModeAfterSetValueMode() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array()
    );
    $select->setValueMode(\Papaya\UI\Dialog\Field\Select::VALUE_USE_CAPTION);
    $this->assertEquals(\Papaya\UI\Dialog\Field\Select::VALUE_USE_CAPTION, $select->getValueMode());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  */
  public function testAppendTo() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array(21 => 'half', 42 => 'full')
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="21">half</option>
          <option value="42">full</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  */
  public function testAppendToWithEmptyValue() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array('' => 'empty', 'some' => 'filled')
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option selected="selected">empty</option>
          <option value="some">filled</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  * @covers \Papaya\UI\Dialog\Field\Select::_createFilter
  */
  public function testAppendToUsingCaptionsAsOptionValues() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption',
      'name',
      array(21 => 'half', 42 => 'full'),
      TRUE,
      \Papaya\UI\Dialog\Field\Select::VALUE_USE_CAPTION
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="half">half</option>
          <option value="full">full</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  */
  public function testAppendToWithIterator() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', new ArrayIterator(array(21 => 'half', 42 => 'full'))
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="21">half</option>
          <option value="42">full</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptionGroup
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  * @covers \Papaya\UI\Dialog\Field\Select::_createFilter
  */
  public function testAppendToWithRecursiveIterator() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption',
      'name',
      new \Papaya\Iterator\Tree\Groups\RegEx(
        array('foo', 'bar', 'foobar'), '(^foo)'
      ),
      TRUE,
      \Papaya\UI\Dialog\Field\Select::VALUE_USE_CAPTION
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <group caption="foo">
            <option value="foo">foo</option>
            <option value="foobar">foobar</option>
          </group>
          <option value="bar">bar</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  * @covers \Papaya\UI\Dialog\Field\Select::_isOptionSelected
  */
  public function testAppendToWithDefaultValue() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('truth' => 42))));
    $dialog
      ->expects($this->any())
      ->method('getParameterName')
      ->with('truth')
      ->willReturnArgument(0);
    $document = new \Papaya\Xml\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'truth', array(21 => 'half', 42 => 'full')
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getCollectionMock($dialog));
    $select->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="no" mandatory="yes">
        <select name="truth" type="dropdown">
          <option value="21">half</option>
          <option value="42" selected="selected">full</option>
        </select>
      </field>',
      $document->saveXML($node->firstChild)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  */
  public function testAppendToWithOptionCaptionCallback() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array(21 => array('title' => 'half'), 42 =>  array('title' => 'full'))
    );
    $select->callbacks()->getOptionCaption = array($this, 'callbackGetOptionCaption');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="21">mapped: half</option>
          <option value="42">mapped: full</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  public function callbackGetOptionCaption(
    /** @noinspection PhpUnusedParameterInspection */
    $context, $data
  ) {
    return 'mapped: '.$data['title'];
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  */
  public function testAppendToWithOptionDataAttributesCallback() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'name', array(21 => 'half', 42 => 'full')
    );
    $select->callbacks()->getOptionData = array($this, 'callbackGetOptionDataAttributes');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="21" data-title="half" data-index="21" data-json="[21,42]">half</option>
          <option value="42" data-title="full" data-index="42" data-json="[21,42]">full</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  public function callbackGetOptionDataAttributes(
    /** @noinspection PhpUnusedParameterInspection */
    $context, $data, $index
  ) {
    return array('title' => $data, 'index' => $index, 'json' => array(21, 42));
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::appendTo
  * @covers \Papaya\UI\Dialog\Field\Select::_appendSelect
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOption
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptionGroup
  * @covers \Papaya\UI\Dialog\Field\Select::_appendOptions
  */
  public function testAppendToWithOptionGroupCallback() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption',
      'name',
      new \Papaya\Iterator\Tree\Groups\RegEx(
        array('foo', 'bar', 'foobar'), '(^foo)'
      ),
      TRUE,
      \Papaya\UI\Dialog\Field\Select::VALUE_USE_CAPTION
    );
    $select->callbacks()->getOptionGroupCaption = array($this, 'callbackGetOptionGroupCaption');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <group caption="Group: foo">
            <option value="foo">foo</option>
            <option value="foobar">foobar</option>
          </group>
          <option value="bar">bar</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  public function callbackGetOptionGroupCaption(
    /** @noinspection PhpUnusedParameterInspection */
    $context, $data
  ) {
    return 'Group: '.$data;
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder(\Papaya\UI\Dialog\Field\Select\Callbacks::class)
      ->disableOriginalConstructor()
      ->getMock();
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'truth', array(21 => 'half', 42 => 'full')
    );
    $this->assertSame(
      $callbacks, $select->callbacks($callbacks)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $select = new \Papaya\UI\Dialog\Field\Select(
      'Caption', 'truth', array(21 => 'half', 42 => 'full')
    );
    $callbacks = $select->callbacks();
    $this->assertInstanceOf(
      \Papaya\BaseObject\Callbacks::class, $callbacks
    );
  }

  /*************************
  * Mocks
  *************************/

  /**
   * @param object|NULL $owner
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog\Fields
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}

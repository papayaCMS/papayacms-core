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

class PapayaContentThemeSetTest extends PapayaTestCase {

  /**
  * @covers PapayaContentThemeSet::_createMapping
  */
  public function testCreateMapping() {
    $themeSet = new PapayaContentThemeSet();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseRecordMapping $mapping */
    $this->assertInstanceOf(
      PapayaDatabaseInterfaceMapping::class,
      $mapping = $themeSet->mapping()
    );
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromPropertyToField));
  }

  /**
  * @covers PapayaContentThemeSet::mapFieldToProperty
  */
  public function testMapFieldToPropertyPassthru() {
    $themeSet = new PapayaContentThemeSet();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseRecordMapping $mapping */
    $mapping = $themeSet->mapping();
    $this->assertEquals(
      'success',
      $mapping->callbacks()->onMapValueFromFieldToProperty(
        'title', 'themeset_title', 'success'
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::mapFieldToProperty
  */
  public function testMapFieldToPropertyUnserialize() {
    $themeSet = new PapayaContentThemeSet();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseRecordMapping $mapping */
    $mapping = $themeSet->mapping();
    $this->assertEquals(
      array(
        'PAGE' => array(
          'GROUP' => array(
            'FOO' => 'bar'
          )
        )
      ),
      $mapping->callbacks()->onMapValueFromFieldToProperty(
        'values',
        'themeset_values',
        /** @lang XML  */
        '<data version="2">
          <data-list name="PAGE">
            <data-list name="GROUP">
              <data-element name="FOO">bar</data-element>
            </data-list>
          </data-list>
        </data>'
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::mapPropertyToField
  */
  public function testMapPropertyToFieldPassthru() {
    $themeSet = new PapayaContentThemeSet();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseRecordMapping $mapping */
    $mapping = $themeSet->mapping();
    $this->assertEquals(
      'success',
      $mapping->callbacks()->onMapValueFromPropertyToField(
        'title', 'themeset_title', 'success'
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::mapPropertyToField
  */
  public function testMapPropertyToFieldSerialize() {
    $themeSet = new PapayaContentThemeSet();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseRecordMapping $mapping */
    $mapping = $themeSet->mapping();
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<data version="2">
        <data-list name="PAGE">
          <data-list name="GROUP">
            <data-element name="FOO">bar</data-element>
          </data-list>
        </data-list>
      </data>',
      $mapping->callbacks()->onMapValueFromPropertyToField(
        'values', 'themeset_values', array('PAGE' => array('GROUP' => array('FOO' => 'bar')))
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::getValuesXml
  */
  public function testGetValuesXml() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructure $definition */
    $definition = $this->createMock(PapayaContentStructure::class);
    $definition
      ->expects($this->once())
      ->method('getXmlDocument')
      ->with(array())
      ->will($this->returnValue(new PapayaXmlDocument));
    $themeSet = new PapayaContentThemeSet();
    $this->assertInstanceOf(PapayaXmlDocument::class, $themeSet->getValuesXml($definition));
  }

  /**
  * @covers PapayaContentThemeSet::setValuesXml
  */
  public function testSetValuesXml() {
    $document = new PapayaXmlDocument();
    $element = $document->appendElement('set');
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructure $definition */
    $definition = $this->createMock(PapayaContentStructure::class);
    $definition
      ->expects($this->once())
      ->method('getArray')
      ->with($this->isInstanceOf(PapayaXmlElement::class))
      ->will($this->returnValue(array('foo' => 'bar')));
    $themeSet = new PapayaContentThemeSet();
    $themeSet->setValuesXml($definition, $element);
    $this->assertEquals(array('foo' => 'bar'), $themeSet->values);
  }
}

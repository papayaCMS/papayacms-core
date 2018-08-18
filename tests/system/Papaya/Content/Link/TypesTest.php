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

namespace Papaya\Content\Link;

require_once __DIR__.'/../../../../bootstrap.php';

class TypesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Link\Types::getResultIterator
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'linktype_id' => 3,
            'linktype_name' => 'external',
            'linktype_is_visisble' => TRUE,
            'linktype_class' => 'externalLink',
            'linktype_target' => '_blank',
            'linktype_popup' => FALSE,
            'linktype_popup_config' => ''
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    $linkTypes = new Types();
    $linkTypes->setDatabaseAccess($databaseAccess);
    $linkTypes->load();
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'name' => 'visible',
          'is_visisble' => TRUE,
          'class' => '',
          'target' => '_self',
          'is_popup' => FALSE,
          'popup_options' => array()
        ),
        2 => array(
          'id' => 2,
          'name' => 'hidden',
          'is_visisble' => FALSE,
          'class' => '',
          'target' => '_self',
          'is_popup' => FALSE,
          'popup_options' => array()
        ),
        3 => array(
          'id' => 3,
          'name' => 'external',
          'is_visisble' => TRUE,
          'class' => 'externalLink',
          'target' => '_blank',
          'is_popup' => FALSE,
          'popup_options' => array()
        )
      ),
      $linkTypes->toArray()
    );
  }

  /**
   * @covers \Papaya\Content\Link\Types::_createMapping
   */
  public function testCreateMapping() {
    $linkTypes = new Types();
    /** @var \Papaya\Database\Interfaces\Mapping $mapping */
    $this->assertInstanceOf(
      \Papaya\Database\Interfaces\Mapping::class,
      $mapping = $linkTypes->mapping()
    );
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromPropertyToField));
  }

  /**
   * @covers \Papaya\Content\Link\Types::mapFieldToProperty
   */
  public function testMapFieldToPropertyPassthru() {
    $linkTypes = new Types();
    /** @var \Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $linkTypes->mapping();
    $this->assertEquals(
      'success',
      $mapping->callbacks()->onMapValueFromFieldToProperty(
        'name', 'linktype_name', 'success'
      )
    );
  }

  /**
   * @covers \Papaya\Content\Link\Types::mapFieldToProperty
   */
  public function testMapFieldToPropertyUnserialize() {
    $linkTypes = new Types();
    /** @var \Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $linkTypes->mapping();
    $this->assertEquals(
      array(
        'foo' => 'bar'
      ),
      $mapping->callbacks()->onMapValueFromFieldToProperty(
        'popup_options',
        'linktype_popup_config',
        /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>'
      )
    );
  }

  /**
   * @covers \Papaya\Content\Link\Types::mapPropertyToField
   */
  public function testMapPropertyToFieldPassthru() {
    $linkTypes = new Types();
    /** @var \Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $linkTypes->mapping();
    $this->assertEquals(
      'success',
      $mapping->callbacks()->onMapValueFromPropertyToField(
        'name', 'linktype_name', 'success'
      )
    );
  }

  /**
   * @covers \Papaya\Content\Link\Types::mapPropertyToField
   */
  public function testMapPropertyToFieldSerialize() {
    $linkTypes = new Types();
    /** @var \Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $linkTypes->mapping();
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $mapping->callbacks()->onMapValueFromPropertyToField(
        'popup_options', 'linktype_popup_config', array('foo' => 'bar')
      )
    );
  }
}

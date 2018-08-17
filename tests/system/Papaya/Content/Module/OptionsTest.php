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

namespace Papaya\Content\Module;

require_once __DIR__.'/../../../../bootstrap.php';

class OptionsTest extends \Papaya\TestCase {

  /**
   * @covers Options::_createMapping
   */
  public function testCreateMapping() {
    $content = new Options();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $content->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
   * @covers       Options::callbackConvertValueByType
   * @dataProvider providePropertiesToFieldsData
   * @param array $expected
   * @param array $properties
   * @param array $fields
   */
  public function testCallbackConvertValueByTypeIntoFields(array $expected, array $properties, array $fields) {
    $content = new Options();
    $this->assertEquals(
      $expected,
      $content->callbackConvertValueByType(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
        $properties,
        $fields
      )
    );
  }

  /**
   * @covers       Options::callbackConvertValueByType
   * @dataProvider provideFieldsToPropertiesData
   * @param array $expected
   * @param array $properties
   * @param array $fields
   */
  public function testCallbackConvertValueByTypeIntoProperties(array $expected, array $properties, array $fields) {
    $content = new Options();
    $this->assertEquals(
      $expected,
      $content->callbackConvertValueByType(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        $properties,
        $fields
      )
    );
  }

  public static function providePropertiesToFieldsData() {
    return array(
      'guid only' => array(
        array(
          'module_guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890'
        )
      ),
      'integer' => array(
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => 42,
          'moduleoption_type' => 'integer'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 42,
          'type' => 'integer'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => 42,
          'moduleoption_type' => 'integer'
        )
      ),
      'array' => array(
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' =>
          /** @lang XML */
            '<data version="2"><data-element name="0">21</data-element><data-element name="1">42</data-element></data>',
          'moduleoption_type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(21, 42),
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => array(21, 42),
          'moduleoption_type' => 'array'
        )
      )
    );
  }

  public static function provideFieldsToPropertiesData() {
    return array(
      'guid only' => array(
        array(
          'guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890'
        )
      ),
      'integer' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 42,
          'type' => 'integer'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 42,
          'type' => 'integer'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => '42',
          'moduleoption_type' => 'integer'
        )
      ),
      'array - serialized' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(21, 42),
          'type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 'a:2:{i:0;i:21;i:1;i:42;}',
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => 'a:2:{i:0;i:21;i:1;i:42;}',
          'moduleoption_type' => 'array'
        )
      ),
      'array - empty' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(),
          'type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => '',
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => '',
          'moduleoption_type' => 'array'
        )
      ),
      'array - xml' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(21, 42),
          'type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' =>
          /** @lang XML */
            '<data version="2"><data-element name="0">21</data-element><data-element name="1">42</data-element></data>',
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' =>
          /** @lang XML */
            '<data version="2"><data-element name="0">21</data-element><data-element name="1">42</data-element></data>',
          'moduleoption_type' => 'array'
        )
      )
    );
  }
}

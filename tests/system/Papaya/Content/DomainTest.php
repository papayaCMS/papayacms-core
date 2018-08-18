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

namespace Papaya\Content;

require_once __DIR__.'/../../../bootstrap.php';

class DomainTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Domain::_createMapping
   */
  public function testCreateMapping() {
    $record = new Domain();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValue));
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
   * @covers \Papaya\Content\Domain::callbackFieldSerialization
   */
  public function testCallbackFieldSerializationSerializeOptions() {
    $record = new Domain();
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="SAMPLE_OPTION">sample data</data-element></data>',
      $record->callbackFieldSerialization(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
        'options',
        'domain_options',
        array('SAMPLE_OPTION' => 'sample data')
      )
    );
  }

  /**
   * @covers \Papaya\Content\Domain::callbackFieldSerialization
   */
  public function testCallbackFieldSerializationUnserializeOptions() {
    $record = new Domain();
    $this->assertEquals(
      array('SAMPLE_OPTION' => 'sample data'),
      $record->callbackFieldSerialization(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        'options',
        'domain_options',
        /** @lang XML */
        '<data version="2"><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
      )
    );
  }

  /**
   * @covers \Papaya\Content\Domain::callbackFieldSerialization
   */
  public function testCallbackFieldSerializationPassthru() {
    $record = new Domain();
    $this->assertEquals(
      'domain.tld',
      $record->callbackFieldSerialization(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        'host',
        'domain_hostname',
        'domain.tld'
      )
    );
  }

  /**
   * @covers \Papaya\Content\Domain::callbackUpdateHostLength
   */
  public function testCallbackUpdateHostLength() {
    $record = new Domain();
    $this->assertEquals(
      array(
        'domain_id' => 42,
        'domain_hostname' => 'domain.tld',
        'domain_hostlength' => 10,
        'domain_scheme' => 0,
        'domain_mode' => Domain::MODE_VIRTUAL_DOMAIN,
        'domain_data' => 'domain data',
        'domain_options' =>
        /** @lang XML */
          '<data><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
      ),
      $record->callbackUpdateHostLength(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
        array(
          'id' => 42,
          'host' => 'domain.tld',
          'host_length' => 0,
          'scheme' => 0,
          'mode' => Domain::MODE_VIRTUAL_DOMAIN,
          'data' => 'domain data',
          'options' => array('SAMPLE_OPTION' => 'sample data')
        ),
        array(
          'domain_id' => 42,
          'domain_hostname' => 'domain.tld',
          'domain_hostlength' => 0,
          'domain_scheme' => 0,
          'domain_mode' => Domain::MODE_VIRTUAL_DOMAIN,
          'domain_data' => 'domain data',
          'domain_options' =>
          /** @lang XML */
            '<data><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
        )
      )
    );
  }

  /**
   * @covers \Papaya\Content\Domain::callbackUpdateHostLength
   */
  public function testCallbackUpdateHostLengthPassthru() {
    $record = new Domain();
    $this->assertEquals(
      array(
        'id' => 42,
        'host' => 'domain.tld',
        'host_length' => 42,
        'scheme' => 0,
        'mode' => Domain::MODE_VIRTUAL_DOMAIN,
        'data' => 'domain data',
        'options' => array('SAMPLE_OPTION' => 'sample data')
      ),
      $record->callbackUpdateHostLength(
        new \stdClass(),
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        array(
          'id' => 42,
          'host' => 'domain.tld',
          'host_length' => 42,
          'scheme' => 0,
          'mode' => Domain::MODE_VIRTUAL_DOMAIN,
          'data' => 'domain data',
          'options' => array('SAMPLE_OPTION' => 'sample data')
        ),
        array(
          'domain_id' => 42,
          'domain_hostname' => 'domain.tld',
          'domain_hostlength' => 0,
          'domain_scheme' => 0,
          'domain_mode' => Domain::MODE_VIRTUAL_DOMAIN,
          'domain_data' => 'domain data',
          'domain_options' =>
          /** @lang XML */
            '<data><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
        )
      )
    );
  }
}

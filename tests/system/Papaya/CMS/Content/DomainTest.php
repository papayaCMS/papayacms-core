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

namespace Papaya\CMS\Content;

require_once __DIR__.'/../../../../bootstrap.php';

class DomainTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Domain::_createMapping
   */
  public function testCreateMapping() {
    $record = new Domain();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValue));
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
   * @covers \Papaya\CMS\Content\Domain
   */
  public function testCallbackFieldSerializationSerializeOptions() {
    $record = new Domain();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
    $this->assertEquals(
    /** @lang XML */
      '<data version="2"><data-element name="SAMPLE_OPTION">sample data</data-element></data>',
      $mapping->callbacks()->onMapValue(
        \Papaya\Database\Interfaces\Mapping::PROPERTY_TO_FIELD,
        'options',
        'domain_options',
        array('SAMPLE_OPTION' => 'sample data')
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Domain
   */
  public function testCallbackFieldSerializationUnserializeOptions() {
    $record = new Domain();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
    $this->assertEquals(
      array('SAMPLE_OPTION' => 'sample data'),
      $mapping->callbacks()->onMapValue(
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        'options',
        'domain_options',
        /** @lang XML */
        '<data version="2"><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Domain
   */
  public function testCallbackFieldSerializationPassthru() {
    $record = new Domain();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
    $this->assertEquals(
      'domain.tld',
      $mapping->callbacks()->onMapValue(
        \Papaya\Database\Interfaces\Mapping::FIELD_TO_PROPERTY,
        'host',
        'domain_hostname',
        'domain.tld'
      )
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Domain
   */
  public function testCallbackUpdateHostLength() {
    $record = new Domain();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
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
      $mapping->callbacks()->onAfterMapping(
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
   * @covers \Papaya\CMS\Content\Domain
   */
  public function testCallbackUpdateHostLengthPassthru() {
    $record = new Domain();
    /** @var \Papaya\Database\Record\Mapping $mapping */
    $mapping = $record->mapping();
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
      $mapping->callbacks()->onAfterMapping(
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

<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentDomainTest extends PapayaTestCase {

  /**
  * @covers PapayaContentDomain::_createMapping
  */
  public function testCreateMapping() {
    $record = new PapayaContentDomain();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseRecordMapping $mapping */
    $mapping = $record->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValue));
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
  * @covers PapayaContentDomain::callbackFieldSerialization
  */
  public function testCallbackFieldSerializationSerializeOptions() {
    $record = new PapayaContentDomain();
    $this->assertEquals(
      /** @lang XML */
      '<data version="2"><data-element name="SAMPLE_OPTION">sample data</data-element></data>',
      $record->callbackFieldSerialization(
        new stdClass(),
        PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD,
        'options',
        'domain_options',
        array('SAMPLE_OPTION' => 'sample data')
      )
    );
  }

  /**
  * @covers PapayaContentDomain::callbackFieldSerialization
  */
  public function testCallbackFieldSerializationUnserializeOptions() {
    $record = new PapayaContentDomain();
    $this->assertEquals(
      array('SAMPLE_OPTION' => 'sample data'),
      $record->callbackFieldSerialization(
        new stdClass(),
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        'options',
        'domain_options',
        /** @lang XML */
        '<data version="2"><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
      )
    );
  }

  /**
  * @covers PapayaContentDomain::callbackFieldSerialization
  */
  public function testCallbackFieldSerializationPassthru() {
    $record = new PapayaContentDomain();
    $this->assertEquals(
      'domain.tld',
      $record->callbackFieldSerialization(
        new stdClass(),
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        'host',
        'domain_hostname',
        'domain.tld'
      )
    );
  }

  /**
  * @covers PapayaContentDomain::callbackUpdateHostLength
  */
  public function testCallbackUpdateHostLength() {
    $record = new PapayaContentDomain();
    $this->assertEquals(
      array(
        'domain_id' => 42,
        'domain_hostname' => 'domain.tld',
        'domain_hostlength' => 10,
        'domain_scheme' => 0,
        'domain_mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
        'domain_data' => 'domain data',
        'domain_options' =>
          /** @lang XML */
          '<data><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
      ),
      $record->callbackUpdateHostLength(
        new stdClass(),
        PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD,
        array(
          'id' => 42,
          'host' => 'domain.tld',
          'host_length' => 0,
          'scheme' => 0,
          'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
          'data' => 'domain data',
          'options' => array('SAMPLE_OPTION' => 'sample data')
        ),
        array(
          'domain_id' => 42,
          'domain_hostname' => 'domain.tld',
          'domain_hostlength' => 0,
          'domain_scheme' => 0,
          'domain_mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
          'domain_data' => 'domain data',
          'domain_options' =>
            /** @lang XML */
            '<data><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
        )
      )
    );
  }

  /**
  * @covers PapayaContentDomain::callbackUpdateHostLength
  */
  public function testCallbackUpdateHostLengthPassthru() {
    $record = new PapayaContentDomain();
    $this->assertEquals(
      array(
        'id' => 42,
        'host' => 'domain.tld',
        'host_length' => 42,
        'scheme' => 0,
        'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
        'data' => 'domain data',
        'options' => array('SAMPLE_OPTION' => 'sample data')
      ),
      $record->callbackUpdateHostLength(
        new stdClass(),
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        array(
          'id' => 42,
          'host' => 'domain.tld',
          'host_length' => 42,
          'scheme' => 0,
          'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
          'data' => 'domain data',
          'options' => array('SAMPLE_OPTION' => 'sample data')
        ),
        array(
          'domain_id' => 42,
          'domain_hostname' => 'domain.tld',
          'domain_hostlength' => 0,
          'domain_scheme' => 0,
          'domain_mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
          'domain_data' => 'domain data',
          'domain_options' =>
            /** @lang XML */
            '<data><data-element name="SAMPLE_OPTION">sample data</data-element></data>'
        )
      )
    );
  }
}

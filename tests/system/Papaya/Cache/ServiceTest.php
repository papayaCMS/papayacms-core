<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaCacheServiceTest extends PapayaTestCase {

  /**
  * @covers PapayaCacheService::__construct
  */
  public function testConstructor() {
    $service = new PapayaCacheService_TestProxy($options = new PapayaCacheConfiguration());
    $this->assertAttributeSame(
      $options, '_options', $service
    );
  }

  /**
  * @covers PapayaCacheService::getCacheIdentifier
  * @dataProvider getCacheIdentifierDataProvider
  */
  public function testGetCacheIdentifier($group, $identifier, $parameters, $expected) {
    $service = new PapayaCacheService_TestProxy();
    $this->assertSame(
      $expected,
      $service->getCacheIdentifier($group, $identifier, $parameters)
    );
  }

  /**
  * @covers PapayaCacheService::getCacheIdentifier
  * @dataProvider getInvalidCacheIdentifierDataProvider
  */
  public function testGetCacheIdentifierExpectingException($group, $identifier, $parameters) {
    $service = new PapayaCacheService_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $service->getCacheIdentifier('', '', '');
  }

  /**
  * @covers PapayaCacheService::getCacheIdentifier
  */
  public function testGetCacheIdentifierToLargeExpectingException() {
    $service = new PapayaCacheService_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $service->getCacheIdentifier('group', 'element', str_repeat('x', 300));
  }

  /**
  * @covers PapayaCacheService::_escapeIdentifierString
  * @dataProvider escapeIdentifierStringDataProvider
  */
  public function testEscapeIdentifierString($string, $expected) {
    $service = new PapayaCacheService_TestProxy();
    $this->assertSame(
      $expected,
      $service->_escapeIdentifierString($string)
    );
  }

  /**
  * @covers PapayaCacheService::_serializeParameters
  * @dataProvider serializeParametersDataProvider
  */
  public function testSerializeParameters($parameters, $expected) {
    $service = new PapayaCacheService_TestProxy();
    $this->assertSame(
      $expected,
      $service->_serializeParameters($parameters)
    );
  }

  /**
  * @covers PapayaCacheService::_getCacheIdentification
  * @dataProvider getCacheIdentificationDataProvider
  */
  public function testGetCacheIdentification($group, $identifier, $parameters, $expected) {
    $service = new PapayaCacheService_TestProxy();
    $this->assertSame(
      $expected,
      $service->_getCacheIdentification($group, $identifier, $parameters)
    );
  }

  /**
  * @covers PapayaCacheService::_getCacheIdentification
  * @dataProvider getInvalidCacheIdentificationDataProvider
  */
  public function testGetCacheIdentificationExpectingError($group, $identifier, $parameters) {
    $service = new PapayaCacheService_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $service->_getCacheIdentification($group, $identifier, $parameters);
  }

  /**************************************
  * Data Providers
  **************************************/

  public static function getCacheIdentifierDataProvider() {
    return array(
      array(
        'GROUP',
        'ELEMENT',
        'PARAMETERS',
        'GROUP/ELEMENT/PARAMETERS'
      ),
      array(
        'GROUP',
        'ELEMENT',
        array('PARAMETER_1', 'PARAMETER_2'),
        'GROUP/ELEMENT/91dc48c3332977db0b09e40ef18a9246'
      ),
      array(
        'GROUP',
        'ELEMENT',
        new stdClass(),
        'GROUP/ELEMENT/f7827bf44040a444ac855cd67adfb502'
      )
    );
  }

  public static function getInvalidCacheIdentifierDataProvider() {
    return array(
      array(
        '',
        '',
        ''
      ),
      array(
        'GROUP',
        '',
        ''
      ),
      array(
        'GROUP',
        'ELEMENT',
        ''
      ),
      array(
        'GROUP',
        'ELEMENT',
        str_repeat('X', 256)
      )
    );
  }

  public static function escapeIdentifierStringDataProvider() {
    return array(
      array('foo', 'foo'),
      array('{}', '%7B%7D'),
      array("\xC3\x84", '%C3%84'),
    );
  }

  public static function serializeParametersDataProvider() {
    return array(
      array('STRING', 'STRING'),
      array(1, '1'),
      array(TRUE, '1'),
      array(FALSE, ''),
      array(array('PARAMETER_1', 'PARAMETER_2'), '91dc48c3332977db0b09e40ef18a9246'),
      array(new stdClass(), 'f7827bf44040a444ac855cd67adfb502'),
    );
  }

  public static function getCacheIdentificationDataProvider() {
    return array(
      array(
        'GROUP',
        'ELEMENT',
        'PARAMETERS',
        array(
          'group' => 'GROUP',
          'element' => 'ELEMENT',
          'parameters' => 'PARAMETERS'
        )
      ),
      array(
        'GROUP',
        'ELEMENT',
        array('PARAMETER_1', 'PARAMETER_2'),
        array(
          'group' => 'GROUP',
          'element' => 'ELEMENT',
          'parameters' => '91dc48c3332977db0b09e40ef18a9246'
        )
      ),
      array(
        'GROUP',
        'ELEMENT',
        new stdClass(),
        array(
          'group' => 'GROUP',
          'element' => 'ELEMENT',
          'parameters' => 'f7827bf44040a444ac855cd67adfb502'
        )
      )
    );
  }

  public static function getInvalidCacheIdentificationDataProvider() {
    return array(
      array(
        '',
        '',
        ''
      ),
      array(
        'GROUP',
        '',
        ''
      ),
      array(
        'GROUP',
        'ELEMENT',
        ''
      )
    );
  }
}

class PapayaCacheService_TestProxy extends PapayaCacheService {

  protected $_options = NULL;

  public function _getCacheIdentification($group, $element, $parameters) {
    return parent::_getCacheIdentification($group, $element, $parameters);
  }

  public function _escapeIdentifierString($string) {
    return parent::_escapeIdentifierString($string);
  }

  public function _serializeParameters($parameters) {
    return parent::_serializeParameters($parameters);
  }

  public function setConfiguration(PapayaCacheConfiguration $configuration) {
    $this->_options = $configuration;
  }

  public function verify($silent = TRUE) {
  }

  public function write($group, $element, $parameters, $data, $expires = NULL) {
  }

  public function read($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
  }

  public function exists($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
  }

  public function created($group, $element, $parameters, $expires, $ifModifiedSince = NULL) {
  }

  public function delete($group = NULL, $element = NULL, $parameters = NULL) {
  }
}
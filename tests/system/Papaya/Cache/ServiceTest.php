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

namespace Papaya\Cache {

  require_once __DIR__.'/../../../bootstrap.php';

  class ServiceTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Cache\Service::__construct
     */
    public function testConstructor() {
      $service = new Service_TestProxy($options = new Configuration());
      $this->assertSame(
        $options, $service->getConfiguration()
      );
    }

    /**
     * @covers \Papaya\Cache\Service::getCacheIdentifier
     * @dataProvider getCacheIdentifierDataProvider
     * @param string $group
     * @param mixed $identifier
     * @param mixed $parameters
     * @param string $expected
     */
    public function testGetCacheIdentifier($group, $identifier, $parameters, $expected) {
      $service = new Service_TestProxy();
      $this->assertSame(
        $expected,
        $service->getCacheIdentifier($group, $identifier, $parameters)
      );
    }

    /**
     * @covers \Papaya\Cache\Service::getCacheIdentifier
     * @dataProvider getInvalidCacheIdentifierDataProvider
     * @param string $group
     * @param mixed $identifier
     * @param mixed $parameters
     */
    public function testGetCacheIdentifierExpectingException($group, $identifier, $parameters) {
      $service = new Service_TestProxy();
      $this->expectException(\InvalidArgumentException::class);
      $service->getCacheIdentifier($group, $identifier, $parameters);
    }

    /**
     * @covers \Papaya\Cache\Service::getCacheIdentifier
     */
    public function testGetCacheIdentifierToLargeExpectingException() {
      $service = new Service_TestProxy();
      $this->expectException(\InvalidArgumentException::class);
      $service->getCacheIdentifier('group', 'element', str_repeat('x', 300));
    }

    /**
     * @covers \Papaya\Cache\Service::_escapeIdentifierString
     * @dataProvider escapeIdentifierStringDataProvider
     * @param string $string
     * @param string $expected
     */
    public function testEscapeIdentifierString($string, $expected) {
      $service = new Service_TestProxy();
      $this->assertSame(
        $expected,
        $service->_escapeIdentifierString($string)
      );
    }

    /**
     * @covers \Papaya\Cache\Service::_serializeParameters
     * @dataProvider serializeParametersDataProvider
     * @param mixed $parameters
     * @param string $expected
     */
    public function testSerializeParameters($parameters, $expected) {
      $service = new Service_TestProxy();
      $this->assertSame(
        $expected,
        $service->_serializeParameters($parameters)
      );
    }

    /**
     * @covers \Papaya\Cache\Service::_getCacheIdentification
     * @dataProvider getCacheIdentificationDataProvider
     * @param string $group
     * @param mixed $identifier
     * @param mixed $parameters
     * @param array $expected
     */
    public function testGetCacheIdentification($group, $identifier, $parameters, array $expected) {
      $service = new Service_TestProxy();
      $this->assertSame(
        $expected,
        $service->_getCacheIdentification($group, $identifier, $parameters)
      );
    }

    /**
     * @covers \Papaya\Cache\Service::_getCacheIdentification
     * @dataProvider getInvalidCacheIdentificationDataProvider
     * @param string $group
     * @param mixed $identifier
     * @param mixed $parameters
     */
    public function testGetCacheIdentificationExpectingError($group, $identifier, $parameters) {
      $service = new Service_TestProxy();
      $this->expectException(\InvalidArgumentException::class);
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
          new \stdClass(),
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
        array('', '__'),
      );
    }

    public static function serializeParametersDataProvider() {
      return array(
        array('STRING', 'STRING'),
        array(1, '1'),
        array(TRUE, '1'),
        array(FALSE, ''),
        array(array('PARAMETER_1', 'PARAMETER_2'), '91dc48c3332977db0b09e40ef18a9246'),
        array(new \stdClass(), 'f7827bf44040a444ac855cd67adfb502'),
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
          new \stdClass(),
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

  class Service_TestProxy extends Service {

    public function _getCacheIdentification($group, $element, $parameters) {
      return parent::_getCacheIdentification($group, $element, $parameters);
    }

    public function _escapeIdentifierString($string) {
      return parent::_escapeIdentifierString($string);
    }

    public function _serializeParameters($parameters) {
      return parent::_serializeParameters($parameters);
    }

    public function setConfiguration(Configuration $configuration) {
      $this->_configuration = $configuration;
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
}

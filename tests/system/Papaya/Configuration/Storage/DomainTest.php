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

use Papaya\Configuration\Storage\Domain;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaConfigurationStorageDomainTest extends \PapayaTestCase {

  /**
   * @covers Domain::__construct
   * @dataProvider provideHostUrls
   * @param string $expectedScheme
   * @param string $expectedHost
   * @param string $hostUrl
   */
  public function testConstructor($expectedScheme, $expectedHost, $hostUrl) {
    $storage = new Domain($hostUrl);
    $this->assertAttributeEquals(
      $expectedScheme, '_scheme', $storage
    );
    $this->assertAttributeEquals(
      $expectedHost, '_host', $storage
    );
  }

  /**
  * @covers Domain::domain
  */
  public function testDomainGetAfterSet() {
    $domain = $this->createMock(\Papaya\Content\Domain::class);
    $storage = new Domain('sample.tld');
    $this->assertSame($domain, $storage->domain($domain));
  }

  /**
  * @covers Domain::domain
  */
  public function testDomainGetImplicitCreate() {
    $storage = new Domain('sample.tld');
    $this->assertInstanceOf(\Papaya\Content\Domain::class, $storage->domain());
  }

  /**
  * @covers Domain::load
  */
  public function testLoad() {
    $domain = $this->createMock(\Papaya\Content\Domain::class);
    $domain
      ->expects($this->once())
      ->method('load')
      ->with(array('host' => 'www.sample.tld', 'scheme' => array(0, 2)))
      ->will($this->returnValue(TRUE));

    $storage = new Domain('https://www.sample.tld');
    $storage->domain($domain);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers Domain::getIterator
  */
  public function testGetIterator() {
    $domain = $this->createMock(\Papaya\Content\Domain::class);
    $domain
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->will($this->returnCallback(array($this, 'callbackGetOptionValue')));

    $storage = new Domain('www.sample.tld');
    $storage->domain($domain);

    $iterator = $storage->getIterator();
    $this->assertEquals(
      array('OPTION' => 'success'),
      iterator_to_array($iterator)
    );
  }

  public function callbackGetOptionValue($option) {
    $options = array(
      'mode' => \Papaya\Content\Domain::MODE_VIRTUAL_DOMAIN,
      'options' => array('OPTION' => 'success')
    );
    return $options[$option];
  }

  public static function provideHostUrls() {
    return array(
      'both' => array(0, 'www.domain.tld', 'www.domain.tld'),
      'http host' => array(1, 'www.domain.tld', 'http://www.domain.tld'),
      'https host' => array(2, 'www.domain.tld', 'https://www.domain.tld')
    );
  }
}

<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaConfigurationStorageDomainTest extends PapayaTestCase {

  /**
  * @covers PapayaConfigurationStorageDomain::__construct
  * @dataProvider provideHostUrls
  */
  public function testConstructor($expectedScheme, $expectedHost, $hostUrl) {
    $storage = new PapayaConfigurationStorageDomain($hostUrl);
    $this->assertAttributeEquals(
      $expectedScheme, '_scheme', $storage
    );
    $this->assertAttributeEquals(
      $expectedHost, '_host', $storage
    );
  }

  /**
  * @covers PapayaConfigurationStorageDomain::domain
  */
  public function testDomainGetAfterSet() {
    $domain = $this->getMock('PapayaContentDomain');
    $storage = new PapayaConfigurationStorageDomain('sample.tld');
    $this->assertSame($domain, $storage->domain($domain));
  }

  /**
  * @covers PapayaConfigurationStorageDomain::domain
  */
  public function testDomainGetImplicitCreate() {
    $storage = new PapayaConfigurationStorageDomain('sample.tld');
    $this->assertInstanceOf('PapayaContentDomain', $storage->domain());
  }

  /**
  * @covers PapayaConfigurationStorageDomain::load
  */
  public function testLoad() {
    $domain = $this->getMock('PapayaContentDomain');
    $domain
      ->expects($this->once())
      ->method('load')
      ->with(array('host' => 'www.sample.tld', 'scheme' => array(0, 2)))
      ->will($this->returnValue(TRUE));

    $storage = new PapayaConfigurationStorageDomain('https://www.sample.tld');
    $storage->domain($domain);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers PapayaConfigurationStorageDomain::getIterator
  */
  public function testGetIterator() {
    $domain = $this->getMock('PapayaContentDomain');
    $domain
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->will($this->returnCallback(array($this, 'callbackGetOptionValue')));

    $storage = new PapayaConfigurationStorageDomain('www.sample.tld');
    $storage->domain($domain);

    $iterator = $storage->getIterator();
    $this->assertEquals(
      array('OPTION' => 'success'),
      iterator_to_array($iterator)
    );
  }

  public function callbackGetOptionValue($option) {
    $options = array(
      'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
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

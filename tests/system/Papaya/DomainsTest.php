<?php
require_once __DIR__.'/../../bootstrap.php';

class PapayaDomainsTest extends PapayaTestCase {

  /**
  * @covers PapayaDomains::getDomainsByPath
  */
  public function testGetDomainsByPath() {
    $domains = new PapayaDomains();
    $domains->domains($this->getDomainDataFixture());
    $this->assertEquals(
      array(
        1 => array(
          'scheme' => PapayaUtilServerProtocol::BOTH,
          'host' => 'www.sample.tld',
          'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
          'data' => 42
        )
      ),
      $domains->getDomainsByPath(array(0, 21, 42, 84))
    );
  }

  /**
  * @covers PapayaDomains::loadLazy
  * @covers PapayaDomains::getDomainsByPath
  */
  public function testGetDefaultDomainsByPath() {
    $domains = new PapayaDomains();
    $domains->domains(
      $this->getDomainDataFixture(
        array(
          1 => array(
            'scheme' => PapayaUtilServerProtocol::BOTH,
            'host' => 'www.test.tld',
            'mode' => PapayaContentDomain::MODE_DEFAULT,
            'data' => ''
          )
        )
      )
    );
    $this->assertEquals(
      array(
        1 => array(
          'scheme' => PapayaUtilServerProtocol::BOTH,
          'host' => 'www.test.tld',
          'mode' => PapayaContentDomain::MODE_DEFAULT,
          'data' => ''
        )
      ),
      $domains->getDomainsByPath(array(0, 21, 42, 84))
    );
  }

  /**
  * @covers PapayaDomains::getDomainByHost
  */
  public function testGetDomainByHost() {
    $domains = new PapayaDomains();
    $domains->domains($this->getDomainDataFixture());
    $this->assertEquals(
      array(
        'scheme' => PapayaUtilServerProtocol::HTTP,
        'host' => '*.test.tld',
        'mode' => PapayaContentDomain::MODE_REDIRECT_DOMAIN,
        'data' => ''
      ),
      $domains->getDomainByHost('www.test.tld', PapayaUtilServerProtocol::HTTP)
    );
  }

  /**
  * @covers PapayaDomains::getDomainByHost
  */
  public function testGetDomainByHostUsingSchemePriority() {
    $domains = new PapayaDomains();
    $domains->domains(
      $this->getDomainDataFixture(
        array(
          1 => array(
            'scheme' => PapayaUtilServerProtocol::BOTH,
            'host' => 'www.test.tld',
            'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
            'data' => 'failed'
          ),
          2 => array(
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => 'www.test.tld',
            'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
            'data' => 'success'
          ),
          3 => array(
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => '*.test.tld',
            'mode' => PapayaContentDomain::MODE_REDIRECT_DOMAIN,
            'data' => 'failed'
          )
        )
      )
    );
    $this->assertEquals(
      array(
        'scheme' => PapayaUtilServerProtocol::HTTP,
        'host' => 'www.test.tld',
        'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
        'data' => 'success'
      ),
      $domains->getDomainByHost('www.test.tld', PapayaUtilServerProtocol::HTTP)
    );
  }

  /**
  * @covers PapayaDomains::getCurrent
  * @covers PapayaDomains::getDomainByHost
  * @backupGlobals enabled
  */
  public function testGetCurrent() {
    $_SERVER['HTTP_HOST'] = 'www.sample.tld';
    $_SERVER['HTTPS'] = 'on';
    $domains = new PapayaDomains();
    $domains->domains($this->getDomainDataFixture());
    $domains->getCurrent();
    $this->assertEquals(
      array(
        'scheme' => PapayaUtilServerProtocol::BOTH,
        'host' => 'www.sample.tld',
        'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
        'data' => 42
      ),
      $domains->getCurrent()
    );
  }

  /**
  * @covers PapayaDomains::getHostVariants
  * @dataProvider provideHostVariants
  */
  public function testGetHostVariants($expected, $host) {
    $domains = new PapayaDomains();
    $this->assertEquals(
      $expected,
      $domains->getHostVariants($host)
    );
  }

  /**
  * @covers PapayaDomains::loadLazy
  */
  public function testLoadLazy() {
    $domains = new PapayaDomains();
    $domains->domains($this->getDomainDataFixture());
    $domains->loadLazy();
    $domains->loadLazy();
  }

  /**
  * @covers PapayaDomains::domains
  */
  public function testDomainsGetAfterSet() {
    $data = $this->createMock(PapayaContentDomains::class);
    $domains = new PapayaDomains();
    $domains->domains($data);
    $this->assertSame($data, $domains->domains());
  }

  /**
  * @covers PapayaDomains::domains
  */
  public function testDomainGetImplicitCreate() {
    $domains = new PapayaDomains();
    $domains->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(PapayaContentDomains::class, $data = $domains->domains());
    $this->assertSame($papaya, $data->papaya());
  }

  /****************************
  * Data Provider
  ****************************/

  public static function provideHostVariants() {
    return array(
      'simple name' => array(
        array(
          'host',
          '*'
        ),
        'host'
      ),
      'uppercase name' => array(
        array('host', '*'),
        'HOST'
      ),
      '2 parts' => array(
        array(
          'host.tld',
          'host.*',
          '*.tld',
          '*'
        ),
        'host.tld'
      ),
      '3 parts' => array(
        array(
          'www.host.tld',
          '*.host.tld',
          'www.host.*',
          '*.host.*',
          'host.*',
          '*.tld',
          '*'
        ),
        'www.host.tld'
      ),
      '4 parts' => array(
        array(
          'community.www.host.tld',
          '*.www.host.tld',
          'community.www.host.*',
          '*.www.host.*',
          '*.host.tld',
          'www.host.*',
          '*.host.*',
          'host.*',
          '*.tld',
          '*'
        ),
        'community.www.host.tld'
      )
    );
  }

  /****************************
  * Fixtures
  ****************************/

  private function getDomainDataFixture($domains = NULL) {
    if (empty($domains)) {
      $domains = array(
        1 => array(
          'scheme' => PapayaUtilServerProtocol::BOTH,
          'host' => 'www.sample.tld',
          'mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN,
          'data' => '42'
        ),
        2 => array(
          'scheme' => PapayaUtilServerProtocol::HTTP,
          'host' => '*.test.tld',
          'mode' => PapayaContentDomain::MODE_REDIRECT_DOMAIN,
          'data' => ''
        )
      );
    }
    $data = $this->createMock(PapayaContentDomains::class);
    $data
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $data
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($domains)));
    return $data;
  }
}

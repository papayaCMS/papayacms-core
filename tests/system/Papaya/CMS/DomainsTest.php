<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS {

  use \Papaya\Utility;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Domains
   */
  class DomainsTest extends \Papaya\TestCase {

    public function testGetDomainsByPath() {
      $domains = new Domains();
      $domains->domains($this->getDomainDataFixture());
      $this->assertEquals(
        [
          1 => [
            'scheme' => Utility\Server\Protocol::BOTH,
            'host' => 'www.sample.tld',
            'mode' => Content\Domain::MODE_VIRTUAL_DOMAIN,
            'data' => 42
          ]
        ],
        $domains->getDomainsByPath([0, 21, 42, 84])
      );
    }

    public function testGetDefaultDomainsByPath() {
      $domains = new Domains();
      $domains->domains(
        $this->getDomainDataFixture(
          [
            1 => [
              'scheme' => Utility\Server\Protocol::BOTH,
              'host' => 'www.test.tld',
              'mode' => Content\Domain::MODE_DEFAULT,
              'data' => ''
            ]
          ]
        )
      );
      $this->assertEquals(
        [
          1 => [
            'scheme' => Utility\Server\Protocol::BOTH,
            'host' => 'www.test.tld',
            'mode' => Content\Domain::MODE_DEFAULT,
            'data' => ''
          ]
        ],
        $domains->getDomainsByPath([0, 21, 42, 84])
      );
    }

    public function testGetDomainByHost() {
      $domains = new Domains();
      $domains->domains($this->getDomainDataFixture());
      $this->assertEquals(
        [
          'scheme' => Utility\Server\Protocol::HTTP,
          'host' => '*.test.tld',
          'mode' => Content\Domain::MODE_REDIRECT_DOMAIN,
          'data' => ''
        ],
        $domains->getDomainByHost('www.test.tld', Utility\Server\Protocol::HTTP)
      );
    }

    public function testGetDomainByHostUsingSchemePriority() {
      $domains = new Domains();
      $domains->domains(
        $this->getDomainDataFixture(
          [
            1 => [
              'scheme' => Utility\Server\Protocol::BOTH,
              'host' => 'www.test.tld',
              'mode' => Content\Domain::MODE_VIRTUAL_DOMAIN,
              'data' => 'failed'
            ],
            2 => [
              'scheme' => Utility\Server\Protocol::HTTP,
              'host' => 'www.test.tld',
              'mode' => Content\Domain::MODE_VIRTUAL_DOMAIN,
              'data' => 'success'
            ],
            3 => [
              'scheme' => Utility\Server\Protocol::HTTP,
              'host' => '*.test.tld',
              'mode' => Content\Domain::MODE_REDIRECT_DOMAIN,
              'data' => 'failed'
            ]
          ]
        )
      );
      $this->assertEquals(
        [
          'scheme' => Utility\Server\Protocol::HTTP,
          'host' => 'www.test.tld',
          'mode' => Content\Domain::MODE_VIRTUAL_DOMAIN,
          'data' => 'success'
        ],
        $domains->getDomainByHost('www.test.tld', Utility\Server\Protocol::HTTP)
      );
    }

    /**
     * @backupGlobals enabled
     */
    public function testGetCurrent() {
      $_SERVER['HTTP_HOST'] = 'www.sample.tld';
      $_SERVER['HTTPS'] = 'on';
      $domains = new Domains();
      $domains->domains($this->getDomainDataFixture());
      $domains->getCurrent();
      $this->assertEquals(
        [
          'scheme' => Utility\Server\Protocol::BOTH,
          'host' => 'www.sample.tld',
          'mode' => Content\Domain::MODE_VIRTUAL_DOMAIN,
          'data' => 42
        ],
        $domains->getCurrent()
      );
    }

    /**
     * @dataProvider provideHostVariants
     * @param array $expected
     * @param string $host
     */
    public function testGetHostVariants(array $expected, $host) {
      $domains = new Domains();
      $this->assertEquals(
        $expected,
        $domains->getHostVariants($host)
      );
    }

    public function testLoadLazy() {
      $domains = new Domains();
      $domains->domains($this->getDomainDataFixture());
      $domains->loadLazy();
      $domains->loadLazy();
    }

    public function testDomainsGetAfterSet() {
      $data = $this->createMock(Content\Domains::class);
      $domains = new Domains();
      $domains->domains($data);
      $this->assertSame($data, $domains->domains());
    }

    public function testDomainGetImplicitCreate() {
      $domains = new Domains();
      $domains->papaya($papaya = $this->mockPapaya()->application());
      $this->assertInstanceOf(Content\Domains::class, $data = $domains->domains());
      $this->assertSame($papaya, $data->papaya());
    }

    /****************************
     * Data Provider
     ****************************/

    public static function provideHostVariants() {
      return [
        'simple name' => [
          [
            'host',
            '*'
          ],
          'host'
        ],
        'uppercase name' => [
          ['host', '*'],
          'HOST'
        ],
        '2 parts' => [
          [
            'host.tld',
            'host.*',
            '*.tld',
            '*'
          ],
          'host.tld'
        ],
        '3 parts' => [
          [
            'www.host.tld',
            '*.host.tld',
            'www.host.*',
            '*.host.*',
            'host.*',
            '*.tld',
            '*'
          ],
          'www.host.tld'
        ],
        '4 parts' => [
          [
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
          ],
          'community.www.host.tld'
        ]
      ];
    }

    /****************************
     * Fixtures
     ****************************/

    /**
     * @param null $domains
     * @return \PHPUnit_Framework_MockObject_MockObject|Content\Domains
     */
    private function getDomainDataFixture($domains = NULL) {
      if (empty($domains)) {
        $domains = [
          1 => [
            'scheme' => Utility\Server\Protocol::BOTH,
            'host' => 'www.sample.tld',
            'mode' => Content\Domain::MODE_VIRTUAL_DOMAIN,
            'data' => '42'
          ],
          2 => [
            'scheme' => Utility\Server\Protocol::HTTP,
            'host' => '*.test.tld',
            'mode' => Content\Domain::MODE_REDIRECT_DOMAIN,
            'data' => ''
          ]
        ];
      }
      $data = $this->createMock(Content\Domains::class);
      $data
        ->expects($this->once())
        ->method('load')
        ->will($this->returnValue(TRUE));
      $data
        ->expects($this->once())
        ->method('getIterator')
        ->will($this->returnValue(new \ArrayIterator($domains)));
      return $data;
    }
  }
}

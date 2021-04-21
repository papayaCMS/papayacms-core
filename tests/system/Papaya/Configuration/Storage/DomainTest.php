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

namespace Papaya\Configuration\Storage {

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Configuration\Storage\Domain
   */
  class DomainTest extends \Papaya\TestCase {

    /**
     * @dataProvider provideHostUrls
     * @param string $expectedScheme
     * @param string $expectedHost
     * @param string $hostUrl
     */
    public function testConstructor($expectedScheme, $expectedHost, $hostUrl) {
      $storage = new Domain($hostUrl);
      $this->assertEquals($expectedScheme, $storage->getScheme());
      $this->assertEquals($expectedHost, $storage->getHost());
    }

    public function testDomainGetAfterSet() {
      $domain = $this->createMock(\Papaya\Content\Domain::class);
      $storage = new Domain('sample.tld');
      $this->assertSame($domain, $storage->domain($domain));
    }

    public function testDomainGetImplicitCreate() {
      $storage = new Domain('sample.tld');
      $this->assertInstanceOf(\Papaya\Content\Domain::class, $storage->domain());
    }

    public function testLoad() {
      $domain = $this->createMock(\Papaya\Content\Domain::class);
      $domain
        ->expects($this->once())
        ->method('load')
        ->with(['host' => 'www.sample.tld', 'scheme' => [0, 2]])
        ->willReturn(TRUE);

      $storage = new Domain('https://www.sample.tld');
      $storage->domain($domain);
      $this->assertTrue($storage->load());
    }

    public function testGetIteratorForVirtualDomain() {
      $domain = $this->createMock(\Papaya\Content\Domain::class);
      $domain
        ->expects($this->atLeastOnce())
        ->method('__get')
        ->willReturnCallback(
          static function ($option) {
            $options = [
              'mode' => \Papaya\Content\Domain::MODE_VIRTUAL_DOMAIN,
              'options' => ['OPTION' => 'success']
            ];
            return $options[$option];
          }
        );

      $storage = new Domain('www.sample.tld');
      $storage->domain($domain);

      $iterator = $storage->getIterator();
      $this->assertEquals(
        ['OPTION' => 'success'],
        iterator_to_array($iterator)
      );
    }

    public function testGetIterator() {
      $domain = $this->createMock(\Papaya\Content\Domain::class);
      $domain
        ->expects($this->atLeastOnce())
        ->method('__get')
        ->willReturnCallback(
          static function ($option) {
            $options = [
              'mode' => \Papaya\Content\Domain::MODE_DEFAULT,
              'options' => ['OPTION' => 'success']
            ];
            return $options[$option];
          }
        );

      $storage = new Domain('www.sample.tld');
      $storage->domain($domain);

      $iterator = $storage->getIterator();
      $this->assertEquals(
        [],
        iterator_to_array($iterator)
      );
    }

    public static function provideHostUrls() {
      return [
        'both' => [0, 'www.domain.tld', 'www.domain.tld'],
        'http host' => [1, 'www.domain.tld', 'http://www.domain.tld'],
        'https host' => [2, 'www.domain.tld', 'https://www.domain.tld']
      ];
    }
  }
}

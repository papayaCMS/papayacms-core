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

namespace Papaya\CMS\Cache\Identifier\Definition {

  require_once __DIR__.'/../../../../../../bootstrap.php';

  class PageTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers       \Papaya\CMS\Cache\Identifier\Definition\Page
     * @dataProvider provideParameterData
     * @param array $expected
     * @param array $parameters
     */
    public function testGetStatus(array $expected, array $parameters) {
      $definition = new Page();
      $definition->papaya(
        $this->mockPapaya()->application(
          [
            'request' => $this->mockPapaya()->request($parameters)
          ]
        )
      );
      $this->assertEquals($expected, $definition->getStatus());
    }

    /**
     * @covers \Papaya\CMS\Cache\Identifier\Definition\Page
     */
    public function testGetStatusForPreviewExpectingFalse() {
      $definition = new Page();
      $definition->papaya(
        $this->mockPapaya()->application(
          [
            'request' => $this->mockPapaya()->request(['preview' => TRUE])
          ]
        )
      );
      $this->assertFalse($definition->getStatus());
    }

    /**
     * @covers \Papaya\CMS\Cache\Identifier\Definition\Page
     */
    public function testGetStatusWithDefinedHttpEnvironment() {
      $environment = $_SERVER;
      $_SERVER = [
        'HTTPS' => 'on',
        'HTTP_HOST' => 'www.sample.tld',
        'SERVER_PORT' => 443
      ];
      $definition = new Page();
      $definition->papaya($this->mockPapaya()->application());
      $this->assertEquals(
        [
          Page::class => [
            'scheme' => 'https',
            'host' => 'www.sample.tld',
            'port' => 443,
            'category_id' => 0,
            'page_id' => 0,
            'language' => '',
            'output_mode' => 'html'
          ]
        ],
        $definition->getStatus()
      );
      $_SERVER = $environment;
    }

    /**
     * @covers \Papaya\CMS\Cache\Identifier\Definition\Page
     */
    public function testGetSources() {
      $definition = new Page();
      $this->assertEquals(
        \Papaya\Cache\Identifier\Definition::SOURCE_URL,
        $definition->getSources()
      );
    }

    public static function provideParameterData() {
      return [
        [
          [
            Page::class => [
              'scheme' => 'http',
              'host' => '',
              'port' => 80,
              'category_id' => 0,
              'page_id' => 0,
              'language' => '',
              'output_mode' => 'html'
            ]
          ],
          []
        ],
        [
          [
            Page::class => [
              'scheme' => 'http',
              'host' => '',
              'port' => 80,
              'category_id' => 0,
              'page_id' => 42,
              'language' => '',
              'output_mode' => 'html'
            ]
          ],
          [
            'page_id' => 42
          ]
        ],
        [
          [
            Page::class => [
              'scheme' => 'http',
              'host' => '',
              'port' => 80,
              'page_id' => 0,
              'category_id' => 21,
              'language' => '',
              'output_mode' => 'html'
            ]
          ],
          [
            'category_id' => 21
          ]
        ],
        [
          [
            Page::class => [
              'scheme' => 'http',
              'host' => '',
              'port' => 80,
              'page_id' => 42,
              'category_id' => 21,
              'language' => 'de',
              'output_mode' => 'xml'
            ]
          ],
          [
            'category_id' => 21,
            'page_id' => 42,
            'language' => 'de',
            'output_mode' => 'xml'
          ]
        ],
        [
          [
            Page::class => [
              'scheme' => 'http',
              'host' => '',
              'port' => 80,
              'category_id' => 0,
              'page_id' => 42,
              'language' => '',
              'output_mode' => 'html'
            ]
          ],
          [
            'page_id' => 42,
            'foo' => 'bar'
          ]
        ],
      ];
    }
  }
}

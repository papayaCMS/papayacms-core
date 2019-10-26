<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Session {

  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Session\Options
   */
  class OptionsTest extends TestCase {

    public function testOptionsGetDefaults() {
      $options = new Options();
      $this->assertSame(Options::CACHE_PRIVATE, $options->cache);
      $this->assertSame(Options::FALLBACK_REWRITE, $options->fallback);
    }

    public function testOptionsGetAfterSetByConstructor() {
      $options = new Options(
        [
          'CACHE' => Options::CACHE_NONE,
          'FALLBACK' => Options::FALLBACK_NONE
        ]
      );
      $this->assertSame(Options::CACHE_NONE, $options->cache);
      $this->assertSame(Options::FALLBACK_NONE, $options->fallback);
    }
  }

}

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

namespace Papaya\Filter\Factory {

  require_once __DIR__.'/../../../../bootstrap.php';

  class ProfileTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Filter\Factory\Profile::options
     */
    public function testOptionsGetAfterSet() {
      $profile = new Profile_TestProxy();
      $profile->options('example');
      $this->assertEquals('example', $profile->options());
    }

    /**
     * @covers \Papaya\Filter\Factory\Profile::options
     */
    public function testOptionGetWithoutSetExpectingFalse() {
      $profile = new Profile_TestProxy();
      $this->assertFalse($profile->options());
    }

  }

  class Profile_TestProxy extends Profile {

    public function getFilter() {
    }
  }
}

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

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsIpAddressV6Test extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsIpAddressV6::getFilter
   */
  public function testGetFilterWithIpV6AddressExpectTrue() {
    $profile = new \Papaya\Filter\Factory\Profile\IsIpAddressV6();
    $this->assertTrue($profile->getFilter()->validate('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsIpAddressV6::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new \Papaya\Filter\Factory\Profile\IsIpAddressV6();
    $this->expectException(\PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }
}

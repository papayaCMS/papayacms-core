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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterFactoryProfileGeneratorTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\Generator::getFilter
   */
  public function testGetFilterWithIntegerMinAndMax() {
    $profile = new \Papaya\Filter\Factory\Profile\Generator();
    $profile->options(array(\Papaya\Filter\IntegerValue::class, 1, 42));
    $filter = $profile->getFilter();
    $this->assertInstanceOf(\Papaya\Filter\IntegerValue::class, $filter);
    $this->assertTrue($filter->validate('21'));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\Generator::getFilter
   */
  public function testGetFilterWithInvalidOptionsExpectingException() {
    $profile = new \Papaya\Filter\Factory\Profile\Generator();
    $profile->options(NULL);
    $this->expectException(\Papaya\Filter\Factory\Exception\InvalidOptions::class);
    $profile->getFilter();
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\Generator::getFilter
   */
  public function testGetFilterWithInvalidFilterClass() {
    $profile = new \Papaya\Filter\Factory\Profile\Generator();
    $profile->options(array(\stdClass::class));
    $this->expectException(\Papaya\Filter\Factory\Exception\InvalidFilter::class);
    $profile->getFilter();
  }
}

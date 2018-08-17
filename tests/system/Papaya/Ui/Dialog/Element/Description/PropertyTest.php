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

namespace Papaya\UI\Dialog\Element\Description;
require_once __DIR__.'/../../../../../../bootstrap.php';

class PropertyTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Element\Description\Property::__construct
   * @covers \Papaya\UI\Dialog\Element\Description\Property::setName
   */
  public function testConstructor() {
    $property = new Property('foo', 'bar');
    $this->assertEquals(
      'foo', $property->name
    );
    $this->assertEquals(
      'bar', $property->value
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Element\Description\Property::appendTo
   */
  public function testAppendTo() {
    $property = new Property('foo', 'bar');
    $this->assertEquals(
    /** @lang XML */
      '<property name="foo" value="bar"/>', $property->getXML()
    );
  }
}

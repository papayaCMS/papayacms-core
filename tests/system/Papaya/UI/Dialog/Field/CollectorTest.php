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

namespace Papaya\UI\Dialog\Field {

  use Papaya\Filter;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\UI\Dialog\Field\Collector
   */
  class CollectorTest extends TestCase {

    public function testCollector() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Filter $filter */
      $filter = $this->createMock(Filter::class);
      $collector = new Collector('a-name', 'default', $filter);
      $collector->setMandatory(TRUE);
      $this->assertSame('a-name', $collector->getName());
      $this->assertSame('default', $collector->getDefaultValue());
      $this->assertSame($filter, $collector->getFilter());
      $this->assertEmpty($collector->getXML());
    }
  }

}

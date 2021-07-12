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

namespace Papaya\UI\Navigation;
require_once __DIR__.'/../../../../bootstrap.php';

class ItemsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Navigation\Items::reference
   */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $items = new Items();
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
   * @covers \Papaya\UI\Navigation\Items::reference
   */
  public function testReferenceImpliciteCreate() {
    $items = new Items();
    $items->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\UI\Reference::class, $reference = $items->reference()
    );
    $this->assertSame(
      $papaya, $reference->papaya()
    );
  }

}

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

namespace Papaya\UI\ListView;
require_once __DIR__.'/../../../../bootstrap.php';

class ColumnsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\ListView\Columns::__construct
   * @covers \Papaya\UI\ListView\Columns::owner
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\ListView $listview */
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $columns = new Columns($listview);
    $this->assertSame(
      $listview, $columns->owner()
    );
  }
}

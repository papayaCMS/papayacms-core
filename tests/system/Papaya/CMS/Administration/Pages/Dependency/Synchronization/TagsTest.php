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

namespace Papaya\CMS\Administration\Pages\Dependency\Synchronization;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class TagsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronizeTags
   */
  public function testSynchronize() {
    $tags = $this->createMock(\Papaya\CMS\Content\Page\Tags::class);
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array(
              1 => array(
                'id' => 1,
                'pageId' => 23
              ),
              2 => array(
                'id' => 2,
                'pageId' => 23
              )
            )
          )
        )
      );
    $tags
      ->expects($this->exactly(2))
      ->method('clear')
      ->with($this->logicalOr(21, 42))
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->exactly(2))
      ->method('insert')
      ->with($this->logicalOr(21, 42), array(1, 2))
      ->will($this->returnValue(TRUE));

    $action = new Tags();
    $action->tags($tags);
    $this->assertTrue($action->synchronize(array(21, 42), 23));
  }


  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronizeTags
   */
  public function testSynchronizeLoadFailed() {
    $tags = $this->createMock(\Papaya\CMS\Content\Page\Tags::class);
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(FALSE));

    $action = new Tags();
    $action->tags($tags);
    $this->assertFalse($action->synchronize(array(21, 42), 23));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronizeTags
   */
  public function testSynchronizeClearOnly() {
    $tags = $this->createMock(\Papaya\CMS\Content\Page\Tags::class);
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(new \ArrayIterator(array()))
      );
    $tags
      ->expects($this->exactly(2))
      ->method('clear')
      ->with($this->logicalOr(21, 42))
      ->will($this->returnValue(TRUE));

    $action = new Tags();
    $action->tags($tags);
    $this->assertTrue($action->synchronize(array(21, 42), 23));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::synchronizeTags
   */
  public function testSynchronizeClearFailedExpectingFalse() {
    $tags = $this->createMock(\Papaya\CMS\Content\Page\Tags::class);
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(new \ArrayIterator(array()))
      );
    $tags
      ->expects($this->once())
      ->method('clear')
      ->with(21)
      ->will($this->returnValue(FALSE));

    $action = new Tags();
    $action->tags($tags);
    $this->assertFalse($action->synchronize(array(21, 42), 23));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::tags
   */
  public function testTagsGetAfterSet() {
    $tags = $this->createMock(\Papaya\CMS\Content\Page\Tags::class);
    $action = new Tags();
    $this->assertSame(
      $tags, $action->tags($tags)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Tags::tags
   */
  public function testTagsGetImplicitCreate() {
    $action = new Tags();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Tags::class, $action->tags()
    );
  }
}

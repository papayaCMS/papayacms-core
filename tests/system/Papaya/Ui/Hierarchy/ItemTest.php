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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiHierarchyItemTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Hierarchy\Item::__construct
  */
  public function testConstructor() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $this->assertEquals('sample', $item->caption);
  }

  /**
   * @covers \Papaya\Ui\Hierarchy\Item::appendTo
   */
  public function testAppendTo() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $item->image = 'items-page';
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      /** @lang XML */
      '<item caption="sample" image="page.png" mode="both"/>',
      $item
    );
  }

  /**
   * @covers \Papaya\Ui\Hierarchy\Item::appendTo
   */
  public function testAppendToWithHint() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $item->image = 'items-page';
    $item->hint = 'Quick Info';
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      /** @lang XML */
      '<item caption="sample" hint="Quick Info" image="page.png" mode="both"/>',
      $item
    );
  }

  /**
  * @covers \Papaya\Ui\Hierarchy\Item::appendTo
  */
  public function testAppendToWithReference() {
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('link.html'));

    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $item->image = 'items-page';
    $item->reference($reference);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      /** @lang XML */
      '<item caption="sample" image="page.png" mode="both" href="link.html"/>',
      $item
    );
  }

  /**
  * @covers \Papaya\Ui\Hierarchy\Item::reference
  */
  public function testItemsGetAfterSet() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $this->assertSame(
      $reference, $item->reference($reference)
    );
  }

  /**
  * @covers \Papaya\Ui\Hierarchy\Item::reference
  */
  public function testItemsGetWithImplicitCreate() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $item->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Ui\Reference::class, $item->reference()
    );
    $this->assertSame(
      $papaya, $item->papaya()
    );
  }

  /**
  * @covers \Papaya\Ui\Hierarchy\Item::setDisplayMode
  */
  public function testSetDisplayMode() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    $item->displayMode = \Papaya\Ui\Hierarchy\Item::DISPLAY_TEXT_ONLY;
    $this->assertEquals(
      \Papaya\Ui\Hierarchy\Item::DISPLAY_TEXT_ONLY, $item->displayMode
    );
  }

  /**
  * @covers \Papaya\Ui\Hierarchy\Item::setDisplayMode
  */
  public function testSetDisplayModeExpectingException() {
    $item = new \Papaya\Ui\Hierarchy\Item('sample');
    try {
      $item->displayMode = -99;
    } catch (OutOfBoundsException $e) {
      $this->assertEquals(
        'Invalid display mode for "Papaya\Ui\Hierarchy\Item".',
        $e->getMessage()
      );
    }
  }
}

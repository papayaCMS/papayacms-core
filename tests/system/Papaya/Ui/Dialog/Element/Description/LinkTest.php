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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogElementDescriptionLinkTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog\Element\Description\Link::appendTo
  */
  public function testAppendTo() {
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('./success.php'));
    $description = new \Papaya\Ui\Dialog\Element\Description\Link();
    $description->reference($reference);
    $this->assertEquals(
      /** @lang XML */
      '<link href="./success.php"/>',
      $description->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Element\Description\Link::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $description = new \Papaya\Ui\Dialog\Element\Description\Link();
    $this->assertSame(
      $reference, $description->reference($reference)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Element\Description\Link::reference
  */
  public function testReferenceGetImplicitCreate() {
    $description = new \Papaya\Ui\Dialog\Element\Description\Link();
    $this->assertInstanceOf(
      \Papaya\Ui\Reference::class, $description->reference()
    );
  }
}

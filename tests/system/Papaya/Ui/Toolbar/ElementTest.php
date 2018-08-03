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

class PapayaUiToolbarElementTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiToolbarElement::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $button = new \PapayaUiToolbarElement_TestProxy();
    $button->reference($reference);
    $this->assertSame(
      $reference, $button->reference()
    );
  }

  /**
  * @covers \PapayaUiToolbarElement::reference
  */
  public function testReferenceGetImplicitCreate() {
    $button = new \PapayaUiToolbarElement_TestProxy();
    $button->papaya(
      $application = $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      \Papaya\Ui\Reference::class, $button->reference()
    );
    $this->assertSame(
      $application, $button->reference()->papaya()
    );
  }

}

class PapayaUiToolbarElement_TestProxy extends \PapayaUiToolbarElement {

  public function appendTo(\Papaya\Xml\Element $parent) {
  }
}

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

namespace Papaya\UI\Toolbar {

  require_once __DIR__.'/../../../../bootstrap.php';

  class ElementTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Toolbar\Element::reference
     */
    public function testReferenceGetAfterSet() {
      $reference = $this->createMock(\Papaya\UI\Reference::class);
      $button = new Element_TestProxy();
      $button->reference($reference);
      $this->assertSame(
        $reference, $button->reference()
      );
    }

    /**
     * @covers \Papaya\UI\Toolbar\Element::reference
     */
    public function testReferenceGetImplicitCreate() {
      $button = new Element_TestProxy();
      $button->papaya(
        $application = $this->mockPapaya()->application()
      );
      $this->assertInstanceOf(
        \Papaya\UI\Reference::class, $button->reference()
      );
      $this->assertSame(
        $application, $button->reference()->papaya()
      );
    }

  }

  class Element_TestProxy extends Element {

    public function appendTo(\Papaya\XML\Element $parent) {
    }
  }
}

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

namespace Papaya\UI\Dialog {

  require_once __DIR__.'/../../../../bootstrap.php';

  class ButtonTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Dialog\Button::__construct
     */
    public function testConstructor() {
      $button = new Button_TestProxy();
      $this->assertEquals(
        Button::ALIGN_RIGHT,
        $button->getAlign()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Button::__construct
     */
    public function testConstructorWithAlign() {
      $button = new Button_TestProxy(Button::ALIGN_LEFT);
      $this->assertEquals(
        Button::ALIGN_LEFT,
        $button->getAlign()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Button::setAlign
     */
    public function testSetAlign() {
      $button = new Button_TestProxy();
      $button->setAlign(Button::ALIGN_LEFT);
      $this->assertEquals(
        Button::ALIGN_LEFT,
        $button->getAlign()
      );
    }
  }

  class Button_TestProxy extends Button {

    public function appendTo(\Papaya\XML\Element $parent) {
    }
  }
}

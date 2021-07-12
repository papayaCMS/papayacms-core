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

namespace Papaya\UI\Control\Command;
require_once __DIR__.'/../../../../../bootstrap.php';

class CallbackTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Control\Command\Callback
   */
  public function testWithValidCallback() {
    $command = new Callback(array($this, 'callbackAppendTo'));
    $this->assertAppendedXmlEqualsXmlFragment(/** @lang XML */
      '<success/>', $command);
  }

  public function callbackAppendTo(\Papaya\XML\Element $parent) {
    return $parent->appendElement('success');
  }

}

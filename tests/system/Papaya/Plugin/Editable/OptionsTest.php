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

namespace Papaya\Plugin\Editable {

  use Papaya\CMS\Plugin\Options as PluginOptions;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Plugin\Editable\Options
   */
  class OptionsTest extends TestCase {

    public function  testModified() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|PluginOptions $data */
      $data = $this->createMock(PluginOptions::class);
      $data
        ->method('getIterator')
        ->willReturn(new \ArrayIterator(['foo' => 'bar']));

      $options = new Options($data);
      $this->assertFalse($options->modified());
      $options['foo'] = 42;
      $this->assertTrue($options->modified());
    }
  }

}

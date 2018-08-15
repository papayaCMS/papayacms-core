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

class PapayaMediaFilePropertiesTest extends \PapayaTestCase {

  public function testFetchPropertiesFromInfoImplementation() {
    $infoMock = $this->createMock(\Papaya\Media\File\Info::class);
    $infoMock
      ->expects($this->once())
      ->method('isSupported')
      ->willReturn(TRUE);
    $infoMock
      ->expects($this->once())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator(array('foo' => 'bar')));
    $info = new \Papaya\Media\File\Properties(__FILE__);
    $info->fetchers($infoMock);

    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($info)
    );
  }

  public function testLazyInitializationOfFetchers() {
    $info = new \Papaya\Media\File\Properties('example.file');
    $this->assertCount(4, $info->fetchers());
  }
}

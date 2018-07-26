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

class PapayaFileSystemFactoryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\File\System\Factory::getFile
   */
  public function testGetFile() {
    $factory = new \Papaya\File\System\Factory();
    $this->assertInstanceOf(\Papaya\File\System\File::class, $factory->getFile('/path/file.txt'));
  }

  /**
   * @covers \Papaya\File\System\Factory::getDirectory
   */
  public function testGetDirectory() {
    $factory = new \Papaya\File\System\Factory();
    $this->assertInstanceOf(\Papaya\File\System\Directory::class, $factory->getDirectory('/path'));
  }
}

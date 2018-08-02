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

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileSelectDirectoryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory\Profile\SelectDirectory
   */
  public function testGetField() {
    $context = $this->createMock(\Papaya\Application\Access::class);
    $context
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($this->mockPapaya()->application()));
    $options = new \Papaya\Ui\Dialog\Field\Factory\Options(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'default' => '',
        'parameters' => array('/sample/'),
        'context' => $context
      )
    );
    $profile = new \Papaya\Ui\Dialog\Field\Factory\Profile\SelectDirectory();
    $profile->fileSystem($this->getFileSystemFixture(array('sample.txt')));
    $profile->options($options);
    $this->assertInstanceOf(\PapayaUiDialogFieldSelect::class, $field = $profile->getField());
  }

  /**
   * @param array|NULL $files
   * @param string $filter
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\File\System\Factory
   */
  private function getFileSystemFixture(array $files = NULL, $filter = '') {
    $directory = $this
      ->getMockBuilder(\Papaya\File\System\Directory::class)
      ->disableOriginalConstructor()
      ->getMock();
    $directory
      ->expects($this->once())
      ->method('isReadable')
      ->will($this->returnValue(NULL !== $files));
    if (NULL !== $files) {
      $directory
        ->expects($this->once())
        ->method('getEntries')
        ->with($filter, \Papaya\File\System\Directory::FETCH_DIRECTORIES)
        ->will($this->returnValue(new ArrayIterator($files)));
    }
    $fileSystem = $this->createMock(\Papaya\File\System\Factory::class);
    $fileSystem
      ->expects($this->once())
      ->method('getDirectory')
      ->with('/sample/')
      ->will($this->returnValue($directory));
    return $fileSystem;
  }
}

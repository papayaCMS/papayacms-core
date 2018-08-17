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

namespace Papaya\UI\Dialog\Field\Factory\Profile {

  require_once __DIR__.'/../../../../../../../bootstrap.php';

  class SelectFileTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile
     */
    public function testGetField() {
      $context = $this->createMock(\Papaya\Application\Access::class);
      $context
        ->expects($this->once())
        ->method('papaya')
        ->will($this->returnValue($this->mockPapaya()->application()));
      $options = new \Papaya\UI\Dialog\Field\Factory\Options(
        array(
          'name' => 'fileselect',
          'caption' => 'File',
          'default' => '',
          'parameters' => array('/sample/'),
          'context' => $context
        )
      );
      $profile = new SelectFile();
      $profile->fileSystem($this->getFileSystemFixture(array('sample.txt')));
      $profile->options($options);
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Select::class, $field = $profile->getField());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile
     */
    public function testGetFieldGetPathFromContext() {
      $context = $this->createMock(SelectFile_TestContext::class);
      $context
        ->expects($this->once())
        ->method('getPath')
        ->will($this->returnValue('/sample/'));
      $options = new \Papaya\UI\Dialog\Field\Factory\Options(
        array(
          'name' => 'fileselect',
          'caption' => 'File',
          'default' => '',
          'parameters' => array('callback:getPath'),
          'context' => $context
        )
      );
      $profile = new SelectFile();
      $profile->fileSystem($this->getFileSystemFixture(array('sample.txt')));
      $profile->options($options);
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Select::class, $field = $profile->getField());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile
     */
    public function testGetFieldWithFilter() {
      $options = new \Papaya\UI\Dialog\Field\Factory\Options(
        array(
          'name' => 'fileselect',
          'caption' => 'File',
          'default' => '',
          'parameters' => array('/', '(pattern)', '/sample/')
        )
      );
      $profile = new SelectFile();
      $profile->fileSystem($this->getFileSystemFixture(array('sample.txt'), '(pattern)'));
      $profile->options($options);
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Select::class, $field = $profile->getField());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile
     */
    public function testGetFieldWithInvalidDirectoryExpectingMessageField() {
      $options = new \Papaya\UI\Dialog\Field\Factory\Options(
        array(
          'name' => 'fileselect',
          'caption' => 'File',
          'default' => '',
          'parameters' => array('/sample/')
        )
      );
      $profile = new SelectFile();
      $profile->fileSystem($this->getFileSystemFixture());
      $profile->options($options);
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Message::class, $field = $profile->getField());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile
     */
    public function testGetFieldGroupsValues() {
      $options = new \Papaya\UI\Dialog\Field\Factory\Options(
        array(
          'name' => 'fileselect',
          'caption' => 'File',
          'mandatory' => TRUE,
          'default' => '',
          'parameters' => array('/sample/')
        )
      );
      $profile = new SelectFile();
      $profile->fileSystem(
        $this->getFileSystemFixture(
          array(
            'group_sample1.txt' => 'group_sample1.txt',
            'group_sample2.txt' => 'group_sample2.txt'
          )
        )
      );
      $profile->options($options);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="File" class="DialogFieldSelect" error="yes" mandatory="yes">
        <select name="fileselect" type="dropdown">
          <group caption="group">
            <option value="group_sample1.txt">group_sample1.txt</option>
            <option value="group_sample2.txt">group_sample2.txt</option>
          </group>
        </select>
      </field>',
        $profile->getField()->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile
     */
    public function testGetFieldNotMandatoryExpectingEmptyElement() {
      $options = new \Papaya\UI\Dialog\Field\Factory\Options(
        array(
          'name' => 'fileselect',
          'caption' => 'File',
          'mandatory' => FALSE,
          'default' => '',
          'parameters' => array('/sample/')
        )
      );
      $profile = new SelectFile();
      $profile->fileSystem(
        $this->getFileSystemFixture(
          array(
            'group_sample1.txt' => 'group_sample1.txt',
            'group_sample2.txt' => 'group_sample2.txt'
          )
        )
      );
      $profile->options($options);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="File" class="DialogFieldSelect" error="no">
        <select name="fileselect" type="dropdown">
          <option selected="selected">none</option>
          <group caption="group">
            <option value="group_sample1.txt">group_sample1.txt</option>
            <option value="group_sample2.txt">group_sample2.txt</option>
          </group>
        </select>
      </field>',
        $profile->getField()->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile::fileSystem
     */
    public function testFileSystemGetAfterSet() {
      $profile = new SelectFile();
      $profile->fileSystem($fileSystem = $this->createMock(\Papaya\File\System\Factory::class));
      $this->assertSame($fileSystem, $profile->fileSystem());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectFile::fileSystem
     */
    public function testFileSystemGetImplicitCreate() {
      $profile = new SelectFile();
      $this->assertInstanceOf(\Papaya\File\System\Factory::class, $profile->fileSystem());
    }

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
          ->with($filter, \Papaya\File\System\Directory::FETCH_FILES)
          ->will($this->returnValue(new \ArrayIterator($files)));
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

  abstract class SelectFile_TestContext extends \Papaya\Application\BaseObject {

    abstract public function getPath();
  }
}

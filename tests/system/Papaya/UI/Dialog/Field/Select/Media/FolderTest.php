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

namespace Papaya\UI\Dialog\Field\Select\Media;

use Papaya\CMS\Content\Media\Folders;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class FolderTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Media\Folder::__construct
   */
  public function testConstructor() {
    $select = new Folder(
      'Caption', 'name'
    );
    $this->assertEquals(
      'Caption', $select->getCaption()
    );
    $this->assertEquals(
      'name', $select->getName()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Media\Folder::mediaFolders
   */
  public function testMediaFoldersGetAfterSet() {
    $select = new Folder(
      'Caption', 'name'
    );
    $select->mediaFolders(
      $mediaFolders = $this->createMock(Folders::class)
    );
    $this->assertSame($mediaFolders, $select->mediaFolders());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Media\Folder::mediaFolders
   */
  public function testMediaFoldersGetImplicitCreate() {
    $select = new Folder(
      'Caption', 'name'
    );
    $select->papaya(
      $this->mockPapaya()->application(
        [
          'administrationLanguage' => $this->mockPapaya()->administrationLanguage()
        ]
      )
    );
    $this->assertInstanceOf(Folders::class, $select->mediaFolders());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Media\Folder::appendTo
   */
  public function testAppendTo() {
    $select = new Folder(
      'Caption', 'name'
    );
    $select->mediaFolders($this->getMediaFoldersFixture());
    $select->papaya($this->mockPapaya()->application());
    $select->setDefaultValue(42);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectMediaFolder" error="no">
        <select name="name" type="dropdown">
          <option value="21">Folder 21</option>
          <option value="42" selected="selected">---&gt;Folder 42</option>
          <option value="84">-----&gt;Folder 84</option>
        </select>
      </field>',
      $select->getXML()
    );
  }

  private function getMediaFoldersFixture() {
    $folders = new \Papaya\Iterator\Tree\Children(
      array(
        '21' => array('id' => 21, 'title' => 'Folder 21'),
        '42' => array('id' => 42, 'title' => 'Folder 42'),
        '84' => array('id' => 84, 'title' => 'Folder 84')
      ),
      array(
        0 => array(21),
        21 => array(42),
        42 => array(84)
      )
    );

    $mediaFolders = $this->createMock(Folders::class);
    $mediaFolders
      ->expects($this->once())#
      ->method('getIterator')
      ->will($this->returnValue($folders));
    $mediaFolders
      ->expects($this->any())#
      ->method('offsetExists')
      ->with(42)
      ->will($this->returnValue(TRUE));
    return $mediaFolders;
  }
}

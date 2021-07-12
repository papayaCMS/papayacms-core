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

namespace Papaya\UI\Dialog\Field {

  use Papaya\Test\TestCase;
  use Papaya\CMS\Reference\Thumbnail as ThumbnailReference;

  TestCase::defineConstantDefaults(
    [
      'PAPAYA_PATH_THUMBFILES',
      'PAPAYA_MEDIADB_SUBDIRECTORIES',
      'PAPAYA_MEDIADB_THUMBSIZE',
      'PAPAYA_THUMBS_FILETYPE',
      'PAPAYA_THUMBS_JPEGQUALITY'
    ]
  );

  /**
   * @covers \Papaya\UI\Dialog\Field\Thumbnail
   */
  class ThumbnailTest extends TestCase {

    public function testAppendTo() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_thumbnail $thumbnail */
      $thumbnail = $this->createMock(\base_thumbnail::class);
      $thumbnail
        ->expects($this->once())
        ->method('getThumbnail')
        ->with('a-media-id', NULL, 100, 100, 'max')
        ->willReturn('a-media-id.png');
      /** @var \PHPUnit_Framework_MockObject_MockObject|ThumbnailReference $thumbnailReference */
      $thumbnailReference = $this->createMock(ThumbnailReference::class);
      $thumbnailReference
        ->expects($this->once())
        ->method('setThumbnailMode')
        ->with('max');
      $thumbnailReference
        ->expects($this->once())
        ->method('setThumbnailSize')
        ->with('100x100');
      $thumbnailReference
        ->expects($this->once())
        ->method('setMediaUri')
        ->with('a-media-id.png');
      $thumbnailReference
        ->expects($this->once())
        ->method('get')
        ->willReturn('a-media-id-link.png');

      $image = new Thumbnail('a-media-id', 'A Caption');
      $image->thumbnailGenerator($thumbnail);
      $image->thumbnailReference($thumbnailReference);

      $this->assertXmlStringEqualsXmlString(
        /** @lang XML */
        '<field caption="A Caption" class="DialogFieldThumbnail" error="no">
          <image mode="max" src="a-media-id-link.png"/>
        </field>',
        $image->getXML()
      );
    }

    public function testThumbnailGeneratorImplicitCreate() {
      $image = new Thumbnail('a-media-id', 'A Caption');
      $this->assertNotNull($image->thumbnailGenerator());
    }
    public function testThumbnailReferenceImplicitCreate() {
      $image = new Thumbnail('a-media-id', 'A Caption');
      $image->papaya($this->mockPapaya()->application(['request' => $this->mockPapaya()->request()]));
      $this->assertNotNull($image->thumbnailReference());
    }
  }

}

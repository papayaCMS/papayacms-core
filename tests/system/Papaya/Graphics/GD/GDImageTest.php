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

namespace Papaya\Graphics\GD {

  use Papaya\Graphics\ImageTypes;

  require_once __DIR__.'/../../../../bootstrap.php';

  class GDImageTest extends \Papaya\TestCase {

    public function testConstructor() {
      $resource = imagecreatetruecolor(21,42);
      $image = new GDImage($resource, $library = $this->createMock(GDLibrary::class));
      $this->assertSame($resource, $image->getResource());
      $this->assertSame($library, $image->getLibrary());
      $this->assertEquals(21, $image->getWidth());
      $this->assertEquals(42, $image->getHeight());
    }


    public function testSave() {
      $library = new GDLibrary();
      $image = $library->create(21,42);
      $stream = fopen('php://temp', 'rwb');
      $this->assertTrue($image->save(ImageTypes::MIMETYPE_PNG, $stream));
      rewind($stream);
      $dataURL = 'data:image/png;base64,'.base64_encode(stream_get_contents($stream));
      $image = $library->load($dataURL);
      $this->assertInstanceOf(GDImage::class, $image);
      $this->assertEquals(21, $image->getWidth());
      $this->assertEquals(42, $image->getHeight());
    }
  }
}

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

namespace Papaya\Controller;

require_once __DIR__.'/../../../bootstrap.php';
\Papaya\TestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_IMAGES',
  'PAPAYA_DB_TBL_MODULES',
  'PAPAYA_DB_TBL_MODULEGROUPS'
);

class ImageTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Controller\Image::getImageGenerator
   * @covers \Papaya\Controller\Image::setImageGenerator
   */
  public function testGetImageGenerator() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\base_imagegenerator $generator */
    $generator = $this->createMock(\base_imagegenerator::class);
    $controller = new Image();
    $controller->setImageGenerator($generator);
    $this->assertSame(
      $generator,
      $controller->getImageGenerator()
    );
  }

  /**
   * @covers \Papaya\Controller\Image::getImageGenerator
   */
  public function testGetImageGeneratorImplicitCreate() {
    $controller = new Image();
    $this->assertInstanceOf(
      \base_imagegenerator::class,
      $controller->getImageGenerator()
    );
  }

  /**
   * @covers \Papaya\Controller\Image::execute
   */
  public function testExecute() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request(
      array(
        'preview' => TRUE,
        'image_identifier' => 'sample'
      )
    );
    $response = $this->mockPapaya()->response();
    $controller = new Image();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\base_imagegenerator $generator */
    $generator = $this->createMock(\base_imagegenerator::class);
    $generator
      ->expects($this->once())
      ->method('loadByIdent')
      ->will($this->returnValue(TRUE));
    $generator
      ->expects($this->once())
      ->method('generateImage')
      ->will($this->returnValue(TRUE));
    $controller->setImageGenerator($generator);
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  /**
   * @covers \Papaya\Controller\Image::execute
   */
  public function testExecuteImageGenerateFailed() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request(
      array(
        'preview' => TRUE,
        'image_identifier' => 'sample'
      )
    );
    $response = $this->mockPapaya()->response();
    $controller = new Image();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\base_imagegenerator $generator */
    $generator = $this->createMock(\base_imagegenerator::class);
    $generator
      ->expects($this->once())
      ->method('loadByIdent')
      ->will($this->returnValue(TRUE));
    $generator
      ->expects($this->once())
      ->method('generateImage')
      ->will($this->returnValue(FALSE));
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      Error::class,
      $controller->execute($application, $request, $response)
    );
  }

  /**
   * @covers \Papaya\Controller\Image::execute
   */
  public function testExecuteInvalidImageIdentifier() {
    $controller = new Image();
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request(
      array(
        'preview' => TRUE,
        'image_identifier' => ''
      )
    );
    $response = $this->mockPapaya()->response();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\base_imagegenerator $generator */
    $generator = $this->createMock(\base_imagegenerator::class);
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      Error::class,
      $controller->execute($application, $request, $response)
    );
  }

  /**
   * @covers \Papaya\Controller\Image::execute
   */
  public function testExecuteInvalidPermission() {
    $application = $this->mockPapaya()->application(
      array(
        'AdministrationUser' => $this->mockPapaya()->user(FALSE)
      )
    );
    $request = $this->mockPapaya()->request(
      array(
        'preview' => FALSE,
        'image_identifier' => 'sample'
      )
    );
    $response = $this->mockPapaya()->response();
    $controller = new Image();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\base_imagegenerator $generator */
    $generator = $this->createMock(\base_imagegenerator::class);
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      Error::class,
      $controller->execute($application, $request, $response)
    );
  }
}

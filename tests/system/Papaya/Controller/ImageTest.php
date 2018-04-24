<?php
require_once __DIR__.'/../../../bootstrap.php';
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_IMAGES',
  'PAPAYA_DB_TBL_MODULES',
  'PAPAYA_DB_TBL_MODULEGROUPS'
);

class PapayaControllerImageTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerImage::setImageGenerator
  */
  public function testSetImageGenerator() {
    $generator = $this->getMock('base_imagegenerator');
    $controller = new PapayaControllerImage();
    $controller->setImageGenerator($generator);
    $this->assertAttributeSame(
      $generator, '_imageGenerator', $controller
    );
  }

  /**
  * @covers PapayaControllerImage::getImageGenerator
  */
  public function testGetImageGenerator() {
    $generator = $this->getMock('base_imagegenerator');
    $controller = new PapayaControllerImage();
    $controller->setImageGenerator($generator);
    $this->assertSame(
      $generator,
      $controller->getImageGenerator()
    );
  }

  /**
  * @covers PapayaControllerImage::getImageGenerator
  */
  public function testGetImageGeneratorImplizitCreate() {
    $controller = new PapayaControllerImage();
    $this->assertInstanceOf(
      'base_imagegenerator',
      $controller->getImageGenerator()
    );
  }

  /**
  * @covers PapayaControllerImage::execute
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
    $controller = new PapayaControllerImage();
    $generator = $this->getMock('base_imagegenerator');
    $generator
      ->expects($this->once())
      ->method('loadByIdent')
      ->will($this->returnValue(TRUE));
    $generator
      ->expects($this->once())
      ->method('generateImage')
      ->will($this->returnValue(TRUE));
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $controller->setImageGenerator($generator);
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }

  /**
  * @covers PapayaControllerImage::execute
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
    $controller = new PapayaControllerImage();
    $generator = $this->getMock('base_imagegenerator');
    $generator
      ->expects($this->once())
      ->method('loadByIdent')
      ->will($this->returnValue(TRUE));
    $generator
      ->expects($this->once())
      ->method('generateImage')
      ->will($this->returnValue(FALSE));
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      'PapayaControllerError',
      $controller->execute($application, $request, $response)
    );
  }

  /**
  * @covers PapayaControllerImage::execute
  */
  public function testExecuteInvalidImageIdentifier() {
    $controller = new PapayaControllerImage();
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request(
      array(
        'preview' => TRUE,
        'image_identifier' => ''
      )
    );
    $response = $this->mockPapaya()->response();
    $generator = $this->getMock('base_imagegenerator');
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      'PapayaControllerError',
      $controller->execute($application, $request, $response)
    );
  }

  /**
  * @covers PapayaControllerImage::execute
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
    $controller = new PapayaControllerImage();
    $generator = $this->getMock('base_imagegenerator');
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      'PapayaControllerError',
      $controller->execute($application, $request, $response)
    );
  }
}

<?php
require_once(dirname(__FILE__).'/../../bootstrap.php');

class PapayaObjectTest extends PapayaTestCase {

  /**
  * @covers PapayaObject::setApplication
  */
  public function testSetApplication() {
    $object = new PapayaObject_TestProxy();
    $app = $this->getMock('PapayaApplication');
    $object->setApplication($app);
    $this->assertAttributeSame(
      $app, '_applicationObject', $object
    );
  }

  /**
  * @covers PapayaObject::getApplication
  */
  public function testGetApplication() {
    $object = new PapayaObject_TestProxy();
    $app = $this->getMock('PapayaApplication');
    $object->setApplication($app);
    $this->assertSame(
      $app,
      $object->getApplication()
    );
  }

  /**
  * @covers PapayaObject::getApplication
  */
  public function testGetApplicationSingleton() {
    $object = new PapayaObject_TestProxy();
    $app = $object->getApplication();
    $this->assertInstanceOf(
      'PapayaApplication',
      $app
    );
    $this->assertAttributeSame(
      $app, '_applicationObject', $object
    );
  }

  /**
  * @covers PapayaObject::papaya
  */
  public function testPapayaGetAfterSet() {
    $object = new PapayaObject_TestProxy();
    $application = $this->getMock('PapayaApplication');
    $this->assertSame($application, $object->papaya($application));
  }

  /**
  * @covers PapayaObject::papaya
  */
  public function testPapayaGetUsingSingleton() {
    $object = new PapayaObject_TestProxy();
    $application = $object->papaya();
    $this->assertInstanceOf('PapayaApplication', $object->papaya());
    $this->assertSame($application, $object->papaya());
  }
}

class PapayaObject_TestProxy extends PapayaObject{
}

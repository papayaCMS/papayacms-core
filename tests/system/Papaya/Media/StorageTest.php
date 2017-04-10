<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaMediaStorageTest extends PapayaTestCase {

  public function testGetServiceDefault() {
    $service = PapayaMediaStorage::getService();
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $serviceTwo = PapayaMediaStorage::getService();
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $this->assertTrue($service === $serviceTwo);
  }

  public function testGetServiceInvalid() {
    $this->setExpectedException('InvalidArgumentException');
    PapayaMediaStorage::getService('InvalidServiceName');
  }

  public function testGetServiceWithConfiguration() {
    $configuration = $this->mockPapaya()->options();
    $service = PapayaMediaStorage::getService('file', $configuration, FALSE);
    $this->assertTrue($service instanceof PapayaMediaStorageService);
  }

  public function testGetServiceNonStatic() {
    $service = PapayaMediaStorage::getService('file', NULL, FALSE);
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $serviceTwo = PapayaMediaStorage::getService('file', NULL, FALSE);
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $this->assertTrue($service !== $serviceTwo);
  }
}

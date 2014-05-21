<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterFactoryProfileTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfile::options
   */
  public function testOptionsGetAfterSet() {
    $profile = new PapayaFilterFactoryProfile_TestProxy();
    $profile->options('example');
    $this->assertEquals('example', $profile->options());
  }

  /**
   * @covers PapayaFilterFactoryProfile::options
   */
  public function testOptionGetWithoutSetExpectingFalse() {
    $profile = new PapayaFilterFactoryProfile_TestProxy();
    $this->assertFalse($profile->options());
  }

}

class PapayaFilterFactoryProfile_TestProxy extends PapayaFilterFactoryProfile {

  public function getFilter() {
  }
}

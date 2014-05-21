<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaFilterFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetIterator() {
    $factory = new PapayaFilterFactory();
    $this->assertContains('isEmail', $factory);
    $this->assertContains('isText', $factory);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testHasProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertTrue($factory->hasProfile('isEmail'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testHasProfileWithLowercaseName() {
    $factory = new PapayaFilterFactory();
    $this->assertTrue($factory->hasProfile('isemail'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testHasProfileExpectingFalse() {
    $factory = new PapayaFilterFactory();
    $this->assertFalse($factory->hasProfile('invalidValidationFilter'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertInstanceOf('PapayaFilterFactoryProfile', $factory->getProfile('isEmail'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetProfileExpectingException() {
    $factory = new PapayaFilterFactory();
    $this->setExpectedException('PapayaFilterFactoryExceptionInvalidProfile');
    $factory->getProfile('SomeInvalidProfileName');
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilter() {
    $profile = $this->getMock('PapayaFilterFactoryProfile');
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->getMock('PapayaFilter')));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile);
    $this->assertInstanceOf('PapayaFilter', $filter);
    $this->assertNotInstanceOf('PapayaFilterLogicalOr', $filter);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilterNotMandatory() {
    $profile = $this->getMock('PapayaFilterFactoryProfile');
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->getMock('PapayaFilter')));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile, FALSE);
    $this->assertInstanceOf('PapayaFilterLogicalOr', $filter);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilterNotMandatoryWithOptions() {
    $profile = $this->getMock('PapayaFilterFactoryProfile');
    $profile
      ->expects($this->once())
      ->method('options')
      ->with('data');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->getMock('PapayaFilter')));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile, TRUE, 'data');
    $this->assertInstanceOf('PapayaFilter', $filter);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilterWithNamedProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertInstanceOf('PapayaFilter', $factory->getFilter(PapayaFilter::IS_EMAIL));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateWithProfileNameExpectingTrue() {
    $this->assertTrue(
      PapayaFilterFactory::validate('foo@bar.tld', PapayaFilter::IS_EMAIL)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateWithEmptyValueExpectingFalse() {
    $this->assertFalse(
      PapayaFilterFactory::validate('', PapayaFilter::IS_EMAIL)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateWithEmptyValueNotMandatoryExpectingTrue() {
    $this->assertTrue(
      PapayaFilterFactory::validate('', PapayaFilter::IS_EMAIL, FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testMatches() {
    $this->assertTrue(
      PapayaFilterFactory::matches('foo', '(^[a-z]+$)')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testMatchesExpectingFalse() {
    $this->assertFalse(
      PapayaFilterFactory::matches('', '(^[a-z]+$)')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testMatchesNotMandatory() {
    $this->assertTrue(
      PapayaFilterFactory::matches('', '(^[a-z]+$)', FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testFilterCastsValue() {
    $this->assertSame(
      42,
      PapayaFilterFactory::filter('42', PapayaFilter::IS_INTEGER, FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethod() {
    $this->assertTrue(
      PapayaFilterFactory::isEmail('foo@bar.tld')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethodExpectingFalse() {
    $this->assertFalse(
      PapayaFilterFactory::isEmail('')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethodNotMandatory() {
    $this->assertTrue(
      PapayaFilterFactory::isEmail('', FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethodWithoutArguments() {
    $this->setExpectedException('InvalidArgumentException');
    /** @noinspection PhpParamsInspection */
    PapayaFilterFactory::isEmail();
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testCallUnknownFunctionExpectingException() {
    $this->setExpectedException('LogicException');
    /** @noinspection PhpUndefinedMethodInspection */
    PapayaFilterFactory::someUnknownFunction();
  }
}
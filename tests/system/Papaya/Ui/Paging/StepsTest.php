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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiPagingStepsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Paging\Steps::__construct
  */
  public function testConstructor() {
    $steps = new \Papaya\UI\Paging\Steps('steps', 20, array(10, 20, 30));
    $this->assertEquals('steps', $steps->parameterName);
    $this->assertEquals(20, $steps->currentStepSize);
    $this->assertEquals(array(10, 20, 30), $steps->stepSizes);
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::appendTo
  */
  public function testAppendTo() {
    $steps = new \Papaya\UI\Paging\Steps('steps', 20, array(10, 20, 30));
    $steps->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging-steps>
        <step-size href="http://www.test.tld/test.html?steps=10">10</step-size>
        <step-size href="http://www.test.tld/test.html?steps=20" selected="selected">20</step-size>
        <step-size href="http://www.test.tld/test.html?steps=30">30</step-size>
      </paging-steps>',
      $steps->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::appendTo
  */
  public function testAppendToWithTraversable() {
    $steps = new \Papaya\UI\Paging\Steps('steps', 20, new ArrayIterator(array(10)));
    $steps->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging-steps>
        <step-size href="http://www.test.tld/test.html?steps=10">10</step-size>
      </paging-steps>',
      $steps->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::appendTo
  */
  public function testAppendToWithAdditionalParameters() {
    $steps = new \Papaya\UI\Paging\Steps('foo/steps', 20, array(10, 20, 30));
    $steps->papaya($this->mockPapaya()->application());
    $steps->reference()->setParameters(array('foo' => array('role' => 42)));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging-steps>
        <step-size href="http://www.test.tld/test.html?foo[role]=42&amp;foo[steps]=10">10</step-size>
        <step-size href="http://www.test.tld/test.html?foo[role]=42&amp;foo[steps]=20"
         selected="selected">20</step-size>
        <step-size href="http://www.test.tld/test.html?foo[role]=42&amp;foo[steps]=30">30</step-size>
      </paging-steps>',
      $steps->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::setXmlNames
  */
  public function testAppendToWithDifferentXml() {
    $steps = new \Papaya\UI\Paging\Steps('foo/steps', 20, array(10, 20, 30));
    $steps->setXmlNames(
      array(
        'list' => 'sizes',
        'item' => 'size'
      )
    );
    $steps->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sizes>
        <size href="http://www.test.tld/test.html?foo[steps]=10">10</size>
        <size href="http://www.test.tld/test.html?foo[steps]=20" selected="selected">20</size>
        <size href="http://www.test.tld/test.html?foo[steps]=30">30</size>
      </sizes>',
      $steps->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::setXmlNames
  */
  public function testSetXmlWithInvalidElement() {
    $steps = new \Papaya\UI\Paging\Steps('foo/steps', 20, array(10, 20, 30));
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid/unknown xml name element "invalid" with value "PagingLinks".');
    $steps->setXmlNames(
      array(
        'invalid' => 'PagingLinks'
      )
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::setXmlNames
  */
  public function testSetXmlWithInvalidElementName() {
    $steps = new \Papaya\UI\Paging\Steps('foo/steps', 20, array(10, 20, 30));
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid/unknown xml name element "list" with value "23Invalid".');
    $steps->setXmlNames(
      array(
        'list' => '23Invalid'
      )
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::getStepSizes
  * @covers \Papaya\UI\Paging\Steps::setStepSizes
  */
  public function testGetStepsAfterSet() {
    $steps = new \Papaya\UI\Paging\Steps('foo/steps', 20, array());
    $steps->stepSizes = array(100, 200);
    $this->assertEquals(
      array(100, 200), $steps->stepSizes
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $steps = new \Papaya\UI\Paging\Steps('steps', 20, array(10, 20, 30));
    $steps->reference($reference);
    $this->assertSame(
      $reference, $steps->reference()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Steps::reference
  */
  public function testReferenceGetImplicitCreate() {
    $steps = new \Papaya\UI\Paging\Steps('steps', 20, array(10, 20, 30));
    $steps->papaya(
      $application = $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      \Papaya\UI\Reference::class, $steps->reference()
    );
    $this->assertSame(
      $application, $steps->reference()->papaya()
    );
  }
}

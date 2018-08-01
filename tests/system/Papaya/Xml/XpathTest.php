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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaXmlXpathTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Xml\Xpath::__construct
  */
  public function testConstructor() {
    $xpath = new \Papaya\Xml\Xpath($document = new \Papaya\Xml\Document());
    $this->assertSame($document, $xpath->document);
    $this->assertEquals(
      version_compare(PHP_VERSION, '<', '5.3.3'),
      $xpath->registerNodeNamespaces()
    );
  }

  /**
  * @covers \Papaya\Xml\Xpath::registerNodeNamespaces
  */
  public function testRegisterNodeNamespaceExpectingTrue() {
    $xpath = new \Papaya\Xml\Xpath($document = new \Papaya\Xml\Document());
    $xpath->registerNodeNamespaces(TRUE);
    $this->assertTrue($xpath->registerNodeNamespaces());
  }

  /**
  * @covers \Papaya\Xml\Xpath::registerNodeNamespaces
  */
  public function testRegisterNodeNamespaceExpectingFalse() {
    $xpath = new \Papaya\Xml\Xpath($document = new \Papaya\Xml\Document());
    $xpath->registerNodeNamespaces(FALSE);
    $this->assertFalse($xpath->registerNodeNamespaces());
  }

  /**
  * @covers \Papaya\Xml\Xpath::registerNamespace
  */
  public function testRegisterNamespacewithAssociatedDOMDocument() {
    $document = new \Papaya\Xml\Document();
    $xpath = new \Papaya\Xml\Xpath($document);
    $this->assertTrue(
      $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom')
    );
  }

  /**
  * @covers \Papaya\Xml\Xpath::registerNamespace
  */
  public function testRegisterNamespaceRegisterNamespaceOnDocument() {
    $document = new \Papaya\Xml\Document();
    $xpath = new \Papaya\Xml\Xpath($document);
    $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
    $this->assertEquals(
      'http://www.w3.org/2005/Atom', $document->getNamespace('atom')
    );
  }

  /**
  * @covers \Papaya\Xml\Xpath::evaluate
  */
  public function testEvaluate() {
    if (version_compare(PHP_VERSION, '<', '5.3.3')) {
      $this->markTestSkipped('PHP Version >= 5.3.3 needed for this test.');
    }
    $document = new \Papaya\Xml\Document();
    $document->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new \Papaya\Xml\Xpath($document);
    $this->assertEquals('success', $xpath->evaluate('string(/sample/@attr)'));
  }

  /**
  * @covers \Papaya\Xml\Xpath::evaluate
  */
  public function testEvaluateWithContext() {
    if (version_compare(PHP_VERSION, '<', '5.3.3')) {
      $this->markTestSkipped('PHP Version >= 5.3.3 needed for this test.');
    }
    $document = new \Papaya\Xml\Document();
    $document->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new \Papaya\Xml\Xpath($document);
    $this->assertEquals('success', $xpath->evaluate('string(@attr)', $document->documentElement));
  }

  /**
  * @covers \Papaya\Xml\Xpath::evaluate
  */
  public function testEvaluateWithNamespaceRegistrationActivated() {
    $document = new \Papaya\Xml\Document();
    $document->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new \Papaya\Xml\Xpath($document);
    $xpath->registerNodeNamespaces(TRUE);
    $this->assertEquals('success', $xpath->evaluate('string(/sample/@attr)'));
  }

  /**
  * @covers \Papaya\Xml\Xpath::evaluate
  */
  public function testEvaluateWithNamespaceRegistrationActivatedAndContext() {
    $document = new \Papaya\Xml\Document();
    $document->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new \Papaya\Xml\Xpath($document);
    $xpath->registerNodeNamespaces(TRUE);
    $this->assertEquals('success', $xpath->evaluate('string(@attr)', $document->documentElement));
  }

  /**
  * @covers \Papaya\Xml\Xpath::query
  */
  public function testQueryExpectingException() {
    $xpath = new \Papaya\Xml\Xpath($document = new \Papaya\Xml\Document());
    $this->expectException(LogicException::class);
    /** @noinspection PhpDeprecationInspection */
    $xpath->query('');
  }
}

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

class PapayaXmlXpathTest extends PapayaTestCase {

  /**
  * @covers PapayaXmlXpath::__construct
  */
  public function testConstructor() {
    $xpath = new PapayaXmlXpath($dom = new PapayaXmlDocument());
    $this->assertSame($dom, $xpath->document);
    $this->assertEquals(
      version_compare(PHP_VERSION, '<', '5.3.3'),
      $xpath->registerNodeNamespaces()
    );
  }

  /**
  * @covers PapayaXmlXpath::registerNodeNamespaces
  */
  public function testRegisterNodeNamespaceExpectingTrue() {
    $xpath = new PapayaXmlXpath($dom = new PapayaXmlDocument());
    $xpath->registerNodeNamespaces(TRUE);
    $this->assertTrue($xpath->registerNodeNamespaces());
  }

  /**
  * @covers PapayaXmlXpath::registerNodeNamespaces
  */
  public function testRegisterNodeNamespaceExpectingFalse() {
    $xpath = new PapayaXmlXpath($dom = new PapayaXmlDocument());
    $xpath->registerNodeNamespaces(FALSE);
    $this->assertFalse($xpath->registerNodeNamespaces());
  }

  /**
  * @covers PapayaXmlXpath::registerNamespace
  */
  public function testRegisterNamespacewithAssociatedDOMDocument() {
    $dom = new PapayaXmlDocument();
    $xpath = new PapayaXmlXpath($dom);
    $this->assertTrue(
      $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom')
    );
  }

  /**
  * @covers PapayaXmlXpath::registerNamespace
  */
  public function testRegisterNamespaceRegisterNamespaceOnDocument() {
    $dom = new PapayaXmlDocument();
    $xpath = new PapayaXmlXpath($dom);
    $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
    $this->assertEquals(
      'http://www.w3.org/2005/Atom', $dom->getNamespace('atom')
    );
  }

  /**
  * @covers PapayaXmlXpath::evaluate
  */
  public function testEvaluate() {
    if (version_compare(PHP_VERSION, '<', '5.3.3')) {
      $this->markTestSkipped('PHP Version >= 5.3.3 needed for this test.');
    }
    $dom = new PapayaXmlDocument();
    $dom->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new PapayaXmlXpath($dom);
    $this->assertEquals('success', $xpath->evaluate('string(/sample/@attr)'));
  }

  /**
  * @covers PapayaXmlXpath::evaluate
  */
  public function testEvaluateWithContext() {
    if (version_compare(PHP_VERSION, '<', '5.3.3')) {
      $this->markTestSkipped('PHP Version >= 5.3.3 needed for this test.');
    }
    $dom = new PapayaXmlDocument();
    $dom->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new PapayaXmlXpath($dom);
    $this->assertEquals('success', $xpath->evaluate('string(@attr)', $dom->documentElement));
  }

  /**
  * @covers PapayaXmlXpath::evaluate
  */
  public function testEvaluateWithNamespaceRegistrationActivated() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new PapayaXmlXpath($dom);
    $xpath->registerNodeNamespaces(TRUE);
    $this->assertEquals('success', $xpath->evaluate('string(/sample/@attr)'));
  }

  /**
  * @covers PapayaXmlXpath::evaluate
  */
  public function testEvaluateWithNamespaceRegistrationActivatedAndContext() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml(/** @lang XML */'<sample attr="success"/>');
    $xpath = new PapayaXmlXpath($dom);
    $xpath->registerNodeNamespaces(TRUE);
    $this->assertEquals('success', $xpath->evaluate('string(@attr)', $dom->documentElement));
  }

  /**
  * @covers PapayaXmlXpath::query
  */
  public function testQueryExpectingException() {
    $xpath = new PapayaXmlXpath($dom = new PapayaXmlDocument());
    $this->expectException(LogicException::class);
    /** @noinspection PhpDeprecationInspection */
    $xpath->query('');
  }
}

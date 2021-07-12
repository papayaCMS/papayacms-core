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

namespace Papaya\XML {

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\XML\Xpath
   */
  class XpathTest extends TestCase {

    public function testConstructor() {
      $xpath = new Xpath($document = new Document());
      $this->assertSame($document, $xpath->document);
      $this->assertEquals(
        version_compare(PHP_VERSION, '5.3.3', '<'),
        $xpath->registerNodeNamespaces()
      );
    }

    public function testRegisterNodeNamespaceExpectingTrue() {
      $xpath = new Xpath($document = new Document());
      $xpath->registerNodeNamespaces(TRUE);
      $this->assertTrue($xpath->registerNodeNamespaces());
    }

    public function testRegisterNodeNamespaceExpectingFalse() {
      $xpath = new Xpath($document = new Document());
      $xpath->registerNodeNamespaces(FALSE);
      $this->assertFalse($xpath->registerNodeNamespaces());
    }

    public function testRegisterNamespaceWithAssociatedDOMDocument() {
      $document = new Document();
      $xpath = new Xpath($document);
      $this->assertTrue(
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom')
      );
    }

    public function testRegisterNamespaceRegisterNamespaceOnDocument() {
      $document = new Document();
      $xpath = new Xpath($document);
      $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
      $this->assertEquals(
        'http://www.w3.org/2005/Atom', $document->getNamespace('atom')
      );
    }

    public function testEvaluate() {
      if (version_compare(PHP_VERSION, '5.3.3', '<')) {
        $this->markTestSkipped('PHP Version >= 5.3.3 needed for this test.');
      }
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<sample attr="success"/>'
      );
      $xpath = new Xpath($document);
      $this->assertEquals('success', $xpath->evaluate('string(/sample/@attr)'));
    }

    public function testEvaluateWithContext() {
      if (version_compare(PHP_VERSION, '5.3.3', '<')) {
        $this->markTestSkipped('PHP Version >= 5.3.3 needed for this test.');
      }
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<sample attr="success"/>'
      );
      $xpath = new Xpath($document);
      $this->assertEquals('success', $xpath->evaluate('string(@attr)', $document->documentElement));
    }

    public function testEvaluateWithContextDoesReturnZeroForNaN() {
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<sample>42</sample>'
      );
      $xpath = new Xpath($document);
      $this->assertEquals(42, $xpath->evaluate('number(/sample)'));
      $this->assertNotEquals(NAN, $xpath->evaluate('number(/non-existing)'));
      $this->assertEquals(0.0, $xpath->evaluate('number(/non-existing)'));
    }

    public function testEvaluateWithNamespaceRegistrationActivated() {
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<sample attr="success"/>'
      );
      $xpath = new Xpath($document);
      $xpath->registerNodeNamespaces(TRUE);
      $this->assertEquals('success', $xpath->evaluate('string(/sample/@attr)'));
    }

    public function testEvaluateWithNamespaceRegistrationActivatedAndContext() {
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<sample attr="success"/>'
      );
      $xpath = new Xpath($document);
      $xpath->registerNodeNamespaces(TRUE);
      $this->assertEquals('success', $xpath->evaluate('string(@attr)', $document->documentElement));
    }

    public function testQueryExpectingException() {
      $xpath = new Xpath($document = new Document());
      $this->expectException(\LogicException::class);
      /** @noinspection PhpDeprecationInspection */
      $xpath->query('');
    }
  }
}

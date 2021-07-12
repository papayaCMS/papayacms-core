<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\Syntax {

  require_once __DIR__.'/../../../../bootstrap.php';

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Database\Syntax\AbstractSyntax
   */
  class AbstractSyntaxTest extends TestCase {

    public function testIdentifierMethodCreatesIdentifierObject() {
      $syntax = new AbstractSyntax_TestProxy(
        $this->createMock(DatabaseConnection::class)
      );
      $identifier = $syntax->identifier('foo');
      $this->assertInstanceOf(Identifier::class, $identifier);
      $this->assertSame('foo', (string)$identifier);
    }

    public function testPlaceholderMethodCreatesPlaceholderObject() {
      $syntax = new AbstractSyntax_TestProxy(
        $this->createMock(DatabaseConnection::class)
      );
      $placeholder = $syntax->placeholder('foo');
      $this->assertInstanceOf(Placeholder::class, $placeholder);
      $this->assertSame(':foo', (string)$placeholder);
    }

    public function testCompileParameterWithIdentifier() {
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->once())
        ->method('quoteIdentifier')
        ->with('field_name')
        ->willReturn('"field_name"');

      $syntax = new AbstractSyntax_TestProxy($connection);
      $this->assertSame(
        '"field_name"',
        $syntax->compileParameter($syntax->identifier('field_name'))
      );
    }

    public function testCompileParameterWithString() {
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->once())
        ->method('quoteString')
        ->with('some text')
        ->willReturn("'some text'");

      $syntax = new AbstractSyntax_TestProxy($connection);
      $this->assertSame("'some text'", $syntax->compileParameter('some text'));
    }

    public function testCompileParameterWithSQLSource() {
      $connection = $this->createMock(DatabaseConnection::class);

      $syntax = new AbstractSyntax_TestProxy($connection);
      $this->assertSame(
        'LOWER(field)',
        $syntax->compileParameter(new SQLSource('LOWER(field)'))
      );
    }

    public function testCompileParameterWithPositionalPlaceholder() {
      $connection = $this->createMock(DatabaseConnection::class);

      $syntax = new AbstractSyntax_TestProxy($connection);
      $this->assertSame(
        '?',
        $syntax->compileParameter('?')
      );
    }
  }

  class AbstractSyntax_TestProxy extends AbstractSyntax {

    public function compileParameter($parameter) {
      return parent::compileParameter($parameter);
    }

    /**
     * @return string
     */
    public function getDialect() {
    }

    /**
     * @param string|Parameter ...$arguments
     * @return SQLSource
     */
    public function concat(...$arguments) {
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function length($text) {
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function like($text) {
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return SQLSource
     */
    public function limit($limit, $offset = 0) {
    }

    /**
     * @param string|Parameter haystack
     * @param string|Parameter $needle
     * @param int|Parameter $offset
     * @return SQLSource
     */
    public function locate($haystack, $needle, $offset = 0) {
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function lower($text) {
    }

    /**
     * @return SQLSource
     */
    public function random() {
    }

    /**
     * @param string|Parameter $haystack
     * @param int|Parameter $offset
     * @param null|int|Parameter $length
     * @return SQLSource
     */
    public function substring($haystack, $offset, $length = NULL) {
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function upper($text) {
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function characterLength($text) {
      // TODO: Implement characterLength() method.
    }

    /**
     * @param string|Parameter $haystack
     * @param string|Parameter $needle
     * @param string|Parameter $thread
     * @return SQLSource
     */
    public function replace($haystack, $needle, $thread) {
      // TODO: Implement replace() method.
    }
  }
}

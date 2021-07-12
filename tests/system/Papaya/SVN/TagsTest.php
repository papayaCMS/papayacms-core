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

namespace Papaya\SVN;
require_once __DIR__.'/../../../bootstrap.php';

/**
 * @covers \Papaya\SVN\Tags
 */
class TagsTest extends \Papaya\TestFramework\TestCase {

  public function testSvnClientSet() {
    $tags = new Tags('');
    $client = $this->createMock(Client::class);
    $this->assertSame(
      $client,
      $tags->svnClient($client)
    );
  }

  public function testSvnClientCreate() {
    $tags = new Tags('');
    $this->assertInstanceOf(
      Client\Extension::class,
      $tags->svnClient()
    );
  }

  public function testHighestRevisionSeen() {
    $expectedUri = 'testuri';
    $expectedRevision = 28;
    $tags = new Tags($expectedUri, $expectedRevision);

    $client = $this->createMock(Client::class);
    $client
      ->expects($this->once())
      ->method('ls')
      ->will($this->returnValue(array()));
    $tags->svnClient($client);

    $this->assertSame(
      $expectedRevision,
      $tags->highestRevisionSeen()
    );
  }

  public function testGetIterator() {
    $expectedUri = 'testuri';
    $tags = new Tags($expectedUri);

    $client = $this->createMock(Client::class);
    $client
      ->expects($this->once())
      ->method('ls')
      ->will($this->returnValue(array()));
    $tags->svnClient($client);

    $this->assertInstanceOf(
      'ArrayIterator',
      $tags->getIterator()
    );
  }

  public function testCount() {
    $expectedUri = 'testuri';
    $tags = new Tags($expectedUri);

    $client = $this->createMock(Client::class);
    $client
      ->expects($this->once())
      ->method('ls')
      ->will($this->returnValue(array()));
    $tags->svnClient($client);

    $this->assertSame(
      0,
      $tags->count()
    );
  }

  public function testFindWithoutRevision() {
    $url = 'http://example.com/foo/tags/foo/';
    $tags = new Tags($url);

    $client = $this->createMock(Client::class);
    $client
      ->expects($this->once())
      ->method('ls')
      ->with($url)
      ->will($this->returnValue(array()));
    $tags->svnClient($client);

    $this->assertSame(
      array(),
      $tags->getIterator()->getArrayCopy()
    );
  }

  /**
   * @dataProvider provideFindExamples
   * @param string $url
   * @param array|FALSE $lsResult
   * @param array $expected
   * @param int $expectedRevision
   */
  public function testFind($url, $lsResult, $expected, $expectedRevision) {
    $tags = new Tags($url, 28);

    $client = $this->createMock(Client::class);
    $client
      ->expects($this->once())
      ->method('ls')
      ->will($this->returnValue($lsResult));
    $tags->svnClient($client);

    $this->assertSame(
      $expected,
      $tags->getIterator()->getArrayCopy()
    );
    $this->assertSame(
      $expectedRevision,
      $tags->highestRevisionSeen()
    );
  }

  public static function provideFindExamples() {
    return array(
      'empty' => array(
        'http://example.com/foo/tags/foo/',
        array(),
        array(),
        28,
      ),
      '1 new' => array(
        'http://example.com/foo/tags/foo/',
        array(
          'version1' => array(
            'created_rev' => 42,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'version1',
            'type' => 'dir',
          ),
        ),
        array('http://example.com/foo/tags/foo/version1'),
        42,
      ),
      '1 new without trailing slash' => array(
        'http://example.com/foo/tags/foo',
        array(
          'version1' => array(
            'created_rev' => 42,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'version1',
            'type' => 'dir',
          ),
        ),
        array('http://example.com/foo/tags/foo/version1'),
        42,
      ),
      '1 old' => array(
        'http://example.com/foo/tags/foo/',
        array(
          'version1' => array(
            'created_rev' => 5,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'version1',
            'type' => 'dir',
          ),
        ),
        array(),
        28,
      ),
      '1 file, no tag' => array(
        'http://example.com/foo/tags/foo/',
        array(
          'testfile.txt' => array(
            'created_rev' => 42,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'testfile.txt',
            'type' => 'file',
          ),
        ),
        array(),
        42,
      ),
      '2 new, 1 old' => array(
        'http://example.com/foo/tags/foo/',
        array(
          'version1' => array(
            'created_rev' => 5,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'version1',
            'type' => 'dir',
          ),
          'version2' => array(
            'created_rev' => 42,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'version2',
            'type' => 'dir',
          ),
          'version1.1' => array(
            'created_rev' => 40,
            'last_author' => 'author',
            'size' => 402,
            'time' => 'Aug 21 2009',
            'time_t' => 1250848652,
            'name' => 'version1.1',
            'type' => 'dir',
          ),
        ),
        array(
          'http://example.com/foo/tags/foo/version2',
          'http://example.com/foo/tags/foo/version1.1'
        ),
        42,
      ),
    );
  }

}

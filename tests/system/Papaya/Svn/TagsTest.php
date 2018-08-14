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

class PapayaSvnTagsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\SVN\Tags::__construct
  */
  public function testConstructWithDefaults() {
    $expectedUri = 'testuri';
    $expectedRevision = 0;
    $tags = new \Papaya\SVN\Tags($expectedUri);
    $this->assertAttributeSame(
      $expectedUri,
      '_tagDirectoryURL',
      $tags
    );
    $this->assertAttributeSame(
      $expectedRevision,
      '_newerThanRevision',
      $tags
    );
    $this->assertAttributeSame(
      $expectedRevision,
      '_highestRevisionSeen',
      $tags
    );
  }

  /**
   * @covers \Papaya\SVN\Tags::__construct
   */
  public function testConstructWithRevision() {
    $expectedUri = 'testuri';
    $expectedRevision = 28;
    $tags = new \Papaya\SVN\Tags($expectedUri, $expectedRevision);
    $this->assertAttributeSame(
        $expectedRevision,
        '_newerThanRevision',
        $tags
    );
    $this->assertAttributeSame(
        $expectedRevision,
        '_highestRevisionSeen',
        $tags
    );
  }

  /**
  * @covers \Papaya\SVN\Tags::svnClient
  */
  public function testSvnClientSet() {
    $tags = new \Papaya\SVN\Tags('');
    $client = $this->createMock(\Papaya\SVN\Client::class);
    $this->assertSame(
      $client,
      $tags->svnClient($client)
    );
  }

  /**
  * @covers \Papaya\SVN\Tags::svnClient
  */
  public function testSvnClientCreate() {
    $tags = new \Papaya\SVN\Tags('');
    $this->assertInstanceOf(
      \Papaya\SVN\Client\Extension::class,
      $tags->svnClient()
    );
  }

  /**
  * @covers \Papaya\SVN\Tags::highestRevisionSeen
  */
  public function testHighestRevisionSeen() {
    $expectedUri = 'testuri';
    $expectedRevision = 28;
    $tags = new \Papaya\SVN\Tags($expectedUri, $expectedRevision);

    $client = $this->createMock(\Papaya\SVN\Client::class);
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

  /**
  * @covers \Papaya\SVN\Tags::getIterator
  */
  public function testGetIterator() {
    $expectedUri = 'testuri';
    $tags = new \Papaya\SVN\Tags($expectedUri);

    $client = $this->createMock(\Papaya\SVN\Client::class);
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

  /**
  * @covers \Papaya\SVN\Tags::count
  */
  public function testCount() {
    $expectedUri = 'testuri';
    $tags = new \Papaya\SVN\Tags($expectedUri);

    $client = $this->createMock(\Papaya\SVN\Client::class);
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

  /**
  * @covers \Papaya\SVN\Tags::find
  */
  public function testFindWithoutRevision() {
    $url = 'http://example.com/foo/tags/foo/';
    $tags = new \Papaya\SVN\Tags($url);

    $client = $this->createMock(\Papaya\SVN\Client::class);
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
   * @covers \Papaya\SVN\Tags::find
   * @dataProvider provideFindExamples
   * @param string $url
   * @param array|FALSE $lsResult
   * @param array $expected
   * @param int $expectedRevision
   */
  public function testFind($url, $lsResult, $expected, $expectedRevision) {
    $tags = new \Papaya\SVN\Tags($url, 28);

    $client = $this->createMock(\Papaya\SVN\Client::class);
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

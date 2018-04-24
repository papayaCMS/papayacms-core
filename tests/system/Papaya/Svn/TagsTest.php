<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaSvnTagsTest extends PapayaTestCase {

  /**
  * @covers PapayaSvnTags::__construct
  */
  public function testConstructWithDefaults() {
    $expectedUri = 'testuri';
    $expectedRevision = 0;
    $tags = new PapayaSvnTags($expectedUri);
    $this->assertAttributeSame(
      $expectedUri,
      '_tagDirectoryUrl',
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
   * @covers PapayaSvnTags::__construct
   */
  public function testConstructWithRevision() {
    $expectedUri = 'testuri';
    $expectedRevision = 28;
    $tags = new PapayaSvnTags($expectedUri, $expectedRevision);
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
  * @covers PapayaSvnTags::svnClient
  */
  public function testSvnClientSet() {
    $tags = new PapayaSvnTags('');
    $client = $this->getMock('PapayaSvnClient');
    $this->assertSame(
      $client,
      $tags->svnClient($client)
    );
  }

  /**
  * @covers PapayaSvnTags::svnClient
  */
  public function testSvnClientCreate() {
    $tags = new PapayaSvnTags('');
    $this->assertInstanceOf(
      'PapayaSvnClientExtension',
      $tags->svnClient()
    );
  }

  /**
  * @covers PapayaSvnTags::highestRevisionSeen
  */
  public function testHighestRevisionSeen() {
    $expectedUri = 'testuri';
    $expectedRevision = 28;
    $tags = new PapayaSvnTags($expectedUri, $expectedRevision);

    $client = $this->getMock('PapayaSvnClient');
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
  * @covers PapayaSvnTags::getIterator
  */
  public function testGetIterator() {
    $expectedUri = 'testuri';
    $tags = new PapayaSvnTags($expectedUri);

    $client = $this->getMock('PapayaSvnClient');
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
  * @covers PapayaSvnTags::count
  */
  public function testCount() {
    $expectedUri = 'testuri';
    $tags = new PapayaSvnTags($expectedUri);

    $client = $this->getMock('PapayaSvnClient');
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
  * @covers PapayaSvnTags::find
  */
  public function testFindWithoutRevision() {
    $url = 'http://example.com/foo/tags/foo/';
    $tags = new PapayaSvnTags($url);

    $client = $this->getMock('PapayaSvnClient');
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
  * @covers PapayaSvnTags::find
  * @dataProvider provideFindExamples
  */
  public function testFind($url, $lsResult, $expected, $expectedRevision) {
    $tags = new PapayaSvnTags($url, 28);

    $client = $this->getMock('PapayaSvnClient');
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

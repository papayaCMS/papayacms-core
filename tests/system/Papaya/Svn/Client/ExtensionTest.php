<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaSvnClientExtensionTest extends PapayaTestCase {

  protected function setUp() {
    if (!extension_loaded('svn')) {
      $this->markTestSkipped(
        'The svn extension is not available.'
      );
    }
  }

  /**
  * @covers PapayaSvnClientExtension::ls
  */
  public function testLs() {
    $svn = new PapayaSvnClientExtension();
    // TODO possibly test by extracting a local svn repo in $this->setUp()
    $this->assertFalse(
      @$svn->ls('file:///not-existing-svn-repo/')
    );
  }

}
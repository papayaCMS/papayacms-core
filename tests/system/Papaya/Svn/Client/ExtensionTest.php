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

class PapayaSvnClientExtensionTest extends \PapayaTestCase {

  protected function setUp() {
    if (!extension_loaded('svn')) {
      $this->markTestSkipped(
        'The svn extension is not available.'
      );
    }
  }

  /**
  * @covers \Papaya\SVN\Client\Extension::ls
  */
  public function testLs() {
    $svn = new \Papaya\SVN\Client\Extension();
    // TODO possibly test by extracting a local svn repo in $this->setUp()
    $this->assertFalse(
      @$svn->ls('file:///not-existing-svn-repo/')
    );
  }

}

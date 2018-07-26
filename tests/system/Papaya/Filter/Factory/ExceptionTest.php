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

class PapayaFilterFactoryExceptionTest extends \PapayaTestCase {

  /**
   * @covers \PapayaFilterFactoryException
   */
  public function testThrowException() {
    $this->expectException(\PapayaFilterFactoryException::class);
    throw new \PapayaFilterFactoryException_TestProxy('Test');
  }

}

class PapayaFilterFactoryException_TestProxy extends \PapayaFilterFactoryException {

  public function getFilter() {
  }
}

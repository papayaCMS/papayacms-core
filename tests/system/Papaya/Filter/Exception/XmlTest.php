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

class PapayaFilterExceptionXmlTest extends PapayaTestCase {

  /**
   * @covers \PapayaFilterExceptionXml
   */
  public function testConstructor() {
    $error = new libxmlError();
    $error->code = 23;
    $error->message = 'libxml fatal error sample';
    $error->line = 42;
    $error->column = 21;
    $error->file = '';

    $exception = new \PapayaFilterExceptionXml(new \PapayaXmlException($error));
    $this->assertNotEmpty($exception->getMessage());
  }
}

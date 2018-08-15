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

namespace Papaya\Controller\Error;

require_once __DIR__.'/../../../../bootstrap.php';

class FileTest extends \PapayaTestCase {

  public function testSetTemplateFile() {
    $controller = new File();
    $fileName = __DIR__.'/TestData/template.txt';
    $this->assertTrue(
      $controller->setTemplateFile($fileName)
    );
    $this->assertStringEqualsFile(
      $fileName,
      $this->readAttribute($controller, '_template')
    );
  }

  public function testSetTemplateFileWithInvalidArgument() {
    $controller = new File();
    $this->assertFalse(
      $controller->setTemplateFile('INVALID_FILENAME.txt')
    );
    $this->assertAttributeNotEquals(
      '', '_template', $controller
    );
  }
}

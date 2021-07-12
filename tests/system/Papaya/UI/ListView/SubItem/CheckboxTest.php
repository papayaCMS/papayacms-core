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

namespace Papaya\UI\ListView\SubItem {

  use Papaya\Request\Parameters;
  use Papaya\TestFramework\TestCase;
  use Papaya\UI\Dialog;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\ListView\SubItem\Checkbox
   */
  class CheckboxTest extends TestCase {

    public function testCheckboxChecked() {
      $item = new Checkbox( $this->getDialogFixture(), 'foo', 'one');
      $this->assertXmlStringEqualsXmlString(
        '<subitem align="left">
          <input name="group[foo][]" type="checkbox" value="one" checked="checked"/>
        </subitem>',
        $item->getXML()
      );
    }

    public function testCheckboxUnchecked() {
      $item = new Checkbox( $this->getDialogFixture(), 'foo', 'two');
      $this->assertXmlStringEqualsXmlString(
        '<subitem align="left">
          <input name="group[foo][]" type="checkbox" value="two"/>
        </subitem>',
        $item->getXML()
      );
    }
    public function testCheckboxCheckedByParameter() {
      $item = new Checkbox( $this->getDialogFixture([], ['foo' => ['one']]), 'foo', 'one');
      $this->assertXmlStringEqualsXmlString(
        '<subitem align="left">
          <input name="group[foo][]" type="checkbox" value="one" checked="checked"/>
        </subitem>',
        $item->getXML()
      );
    }

    /**
     * @param array $parameterValues
     * @param array $dataValues
     * @return \PHPUnit_Framework_MockObject_MockObject|Dialog
     */
    private function getDialogFixture($dataValues = ['foo' => ['one']], $parameterValues = []) {
      $parameters = new Parameters($parameterValues);
      $data = new Parameters($dataValues);
      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('getParameterName')
        ->willReturn(new Parameters\Name('field'));
      $dialog
        ->method('parameterGroup')
        ->willReturn('group');
      $dialog
        ->method('parameters')
        ->willReturn($parameters);
      $dialog
        ->method('data')
        ->willReturn($data);
      return $dialog;
    }
  }
}



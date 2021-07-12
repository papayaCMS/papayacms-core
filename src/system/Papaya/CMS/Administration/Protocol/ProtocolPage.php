<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\Protocol {

  use Papaya\CMS\Administration\Page as AdministrationPage;
  use Papaya\CMS\Administration\PageParameters;

  class ProtocolPage extends AdministrationPage {

    const PARAMETER_NAME_COMMAND = 'cmd';
    const PARAMETER_NAME_PAGE = 'page';
    const PARAMETER_NAME_GROUP = 'group';
    const PARAMETER_NAME_SEVERITY = 'severity';
    const PARAMETER_NAME_ENTRY = 'entry';

    protected $_parameterGroup = 'protocol';

    protected function createContent() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_CONTENT, '60%');
      return new ProtocolContent($this);
    }

    protected function createNavigation() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_NAVIGATION, '40%');
      return new ProtocolNavigation($this);
    }
  }
}

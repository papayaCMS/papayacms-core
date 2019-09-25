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
namespace Papaya\Administration\LinkTypes\Editor;

use Papaya\Administration\Page\Part as AdministrationPagePart;
use Papaya\Content\Link\Type as LinkType;
use Papaya\UI\Control\Command\Controller as CommandsController;

class Commands extends AdministrationPagePart {
  /**
   * @var LinkType
   */
  private $_linkType;

  /**
   * Commands, actual actions
   *
   * @param string $name
   * @param string $default
   *
   * @return CommandsController
   */
  protected function _createCommands($name = 'cmd', $default = 'edit') {
    $commands = new CommandsController('cmd');
    $commands->owner($this);
    $commands['edit'] = new Commands\Change($this->linkType());
    $commands['delete'] = new Commands\Remove($this->linkType());
    return $commands;
  }

  /**
   * The theme skin the the database record wrapper object.
   *
   * @param LinkType $linkType
   *
   * @return LinkType
   */
  public function linkType(LinkType $linkType = NULL) {
    if (NULL !== $linkType) {
      $this->_linkType = $linkType;
    } elseif (NULL === $this->_linkType) {
      $this->_linkType = new LinkType();
    }
    return $this->_linkType;
  }
}

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
namespace Papaya\CMS\Administration\Media\MimeTypes\Editor {

  use Papaya\CMS\Administration\Page\Part as AdministrationPagePart;
  use Papaya\CMS\Content\Media\MimeType;
  use Papaya\CMS\Content\Media\MimeType\Group as MimeTypeGroup;
  use Papaya\UI\Control\Command\Controller as CommandsController;
  use Papaya\Utility\File\Path as FilePathUtilities;

  class Commands extends AdministrationPagePart {
    /**
     * @var MimeTypeGroup
     */
    private $_mimeTypeGroup;
    /**
     * @var MimeType
     */
    private $_mimeType;

    /**
     * Commands, actual actions
     *
     * @param string $name
     * @param string $default
     *
     * @return CommandsController
     */
    protected function _createCommands($name = 'cmd', $default = 'group_edit') {
      $commands = new CommandsController('cmd', $default);
      $commands->owner($this);
      $commands['group_edit'] = new Commands\ChangeGroup($this->mimeTypeGroup(), $this->getLocalIconPath());
      $commands['group_delete'] = new Commands\RemoveGroup($this->mimeTypeGroup());
      $commands['type_edit'] = new Commands\ChangeType($this->mimeType(), $this->getLocalIconPath());
      $commands['type_delete'] = new Commands\RemoveType($this->mimeType());
      return $commands;
    }

    /**
     * The theme skin the the database record wrapper object.
     *
     * @param MimeTypeGroup $mimeTypeGroup
     * @return MimeTypeGroup
     */
    public function mimeTypeGroup(MimeTypeGroup $mimeTypeGroup = NULL) {
      if (NULL !== $mimeTypeGroup) {
        $this->_mimeTypeGroup = $mimeTypeGroup;
      } elseif (NULL === $this->_mimeTypeGroup) {
        $this->_mimeTypeGroup = new MimeTypeGroup();
      }
      return $this->_mimeTypeGroup;
    }

    /**
     * The theme skin the the database record wrapper object.
     *
     * @param MimeType $mediaGroup
     * @return MimeType
     */
    public function mimeType(MimeType $mediaGroup = NULL) {
      if (NULL !== $mediaGroup) {
        $this->_mimeType = $mediaGroup;
      } elseif (NULL === $this->_mimeType) {
        $this->_mimeType = new MimeType();
      }
      return $this->_mimeType;
    }

    public function getLocalIconPath() {
      $path = dirname(__DIR__, 3).'/Assets/Icons/16x16/mimetypes';
      return FilePathUtilities::cleanup($path, FALSE);
    }
  }
}

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
namespace Papaya\Administration\Media\MimeTypes\Editor {

  use Papaya\Administration\Page\Part as AdministrationPagePart;
  use Papaya\Content\Media\MimeType\Group as MediaTypeGroup;
  use Papaya\UI\Control\Command\Controller as CommandsController;

  class Commands extends AdministrationPagePart {
    /**
     * @var MediaTypeGroup
     */
    private $_mediaGroup;

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
      $commands['group_edit'] = new Commands\ChangeGroup($this->mediaGroup(), $this->getLocalIconPath());
      //$commands['group_delete'] = new Commands\RemoveGroup($this->mediaGroup());
      return $commands;
    }

    /**
     * The theme skin the the database record wrapper object.
     *
     * @param MediaTypeGroup $mediaGroup
     * @return MediaTypeGroup
     */
    public function mediaGroup(MediaTypeGroup $mediaGroup = NULL) {
      if (NULL !== $mediaGroup) {
        $this->_mediaGroup = $mediaGroup;
      } elseif (NULL === $this->_mediaGroup) {
        $this->_mediaGroup = new MediaTypeGroup();
      }
      return $this->_mediaGroup;
    }

    public function getLocalIconPath() {
      $path = \Papaya\Utility\File\Path::getBasePath(TRUE);
      $path .= $this->papaya()->options->get('PAPAYA_PATH_ADMIN', '/papaya');
      $path .= '/pics/icons/16x16/mimetypes';
      return \Papaya\Utility\File\Path::cleanup($path, FALSE);
    }
  }
}

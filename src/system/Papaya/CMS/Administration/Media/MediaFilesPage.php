<?php
/*
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

namespace Papaya\CMS\Administration\Media {

  use Papaya\CMS\Administration\Page as AdministrationPage;
  use Papaya\CMS\Administration\PageParameters;

  class MediaFilesPage extends AdministrationPage {

    public const PARAMETER_COMMAND = 'cmd';
    public const COMMAND_ADD_FOLDER = 'add-folder';
    public const COMMAND_EDIT_FOLDER = 'edit-folder';
    public const COMMAND_EDIT_FILE = 'edit-file';
    public const PARAMETER_FOLDER = 'folder-id';
    public const PARAMETER_FILE = 'file-id';
    public const PARAMETER_FILES = 'files';
    public const PARAMETER_FILES_VIEW = 'view';
    public const PARAMETER_FILES_SORT = 'files-sort';
    public const PARAMETER_FILES_LIMIT = 'files-limit';
    public const PARAMETER_FILES_OFFSET = 'files-offset';
    public const PARAMETER_NAVIGATION_MODE = 'navigation';
    public const NAVIGATION_MODE_FOLDERS = 'folders';
    public const NAVIGATION_MODE_TAGS = 'tags';
    public const NAVIGATION_MODE_SEARCH = 'search';
    public const PARAMETER_FILES_ACTION = 'files-action';
    public const FILES_ACTION_TAG = 'tag';
    public const FILES_ACTION_DELETE = 'delete';
    public const FILES_ACTION_MOVE = 'move';
    public const FILES_ACTION_CUT = 'cut';

    protected $_parameterGroup = 'media';

    protected function createContent() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_CONTENT, '50%');
      return new MediaFilesContent($this);
    }

    protected function createNavigation() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_NAVIGATION, '50%');
      return new MediaFilesNavigation($this);
    }
  }
}

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

namespace Papaya\Administration\Settings\Profiles {

  use FilesystemIterator;
  use http\Exception\BadMethodCallException;
  use Papaya\Administration\Settings\SettingProfile;
  use Papaya\Administration\Settings\SettingsPage;
  use Papaya\Configuration\Path;
  use Papaya\Filter\File\Path as FilePathFilter;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Filter\Callback as CallbackFilterIterator;
  use Papaya\Iterator\Union;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated;
  use Papaya\Utility\Bitwise;

  class FileSystemChoiceSetting extends SettingProfile {

    const INCLUDE_FILES = 1;
    const INCLUDE_DIRECTORIES = 2;
    const INCLUDE_OPTION_NONE = 4;

    /**
     * @var Path
     */
    private $_path;
    /**
     * @var null
     */
    private $_filter;
    /**
     * @var int
     */
    private $_flags;

    public function __construct($pathIdentifier, $subPath = '', $filter = NULL, $flags = self::INCLUDE_FILES) {
      $this->_path = new Path($pathIdentifier, $subPath);
      $this->_filter = $filter;
      $this->_flags = $flags;
    }

    public function appendFieldTo(Dialog $dialog, $settingName) {
      $dialog->fields[] = new Dialog\Field\Select(
        $settingName,
        SettingsPage::PARAMETER_SETTING_VALUE,
        $this->getChoices()
      );
      return TRUE;
    }

    private function getChoices() {
      $path = $this->_path;
      $path->papaya($this->papaya());
      $iterator = new FilesystemIterator(
        (string)$path,
        FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
      );
      $choices = new CallbackIterator(
        new CallbackFilterIterator(
          $iterator,
           $this->getFilterFunction()
        ),
        static function (\splFileInfo $fileInfo) {
          return $fileInfo->getBasename();
        }
      );
      if (Bitwise::inBitmask(self::INCLUDE_OPTION_NONE, $this->_flags)) {
        return new Union(
          ['' => new Translated('None')],
          $choices
        );
      }
      return $choices;
    }

    private function getFilterFunction() {
      $filter = $this->_filter;
      if (is_string($filter)) {
        $filter = function (\splFileInfo $fileInfo) {
          return preg_match($this->_filter, $fileInfo->getBasename());
        };
      } elseif (NULL === $this->_filter) {
        $filter = static function () {
          return TRUE;
        };
      }
      if (!($filter instanceof \Closure)) {
        throw new \BadMethodCallException(sprintf('Invalid filter for %s', get_class($this)));
      }
      $includeDirectories = Bitwise::inBitmask(self::INCLUDE_DIRECTORIES, $this->_flags);
      $includeFiles = Bitwise::inBitmask(self::INCLUDE_FILES, $this->_flags);
      if ($includeDirectories && $includeFiles) {
        return $filter;
      }
      if ($includeDirectories) {
        return static function(\splFileInfo $fileInfo) use ($filter) {
          return $fileInfo->isDir() && $filter($fileInfo);
        };
      }
      if ($includeFiles) {
        return static function(\splFileInfo $fileInfo) use ($filter) {
          return $fileInfo->isFile() && $filter($fileInfo);
        };
      }
      return static function () {
        return FALSE;
      };
    }
  }
}


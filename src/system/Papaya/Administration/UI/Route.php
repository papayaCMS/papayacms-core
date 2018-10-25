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
namespace Papaya\Administration\UI {

  interface Route {
    const OVERVIEW = 'overview';

    const MESSAGES = 'messages';

    const MESSAGES_TASKS = self::MESSAGES.'.tasks';

    const PAGES = 'pages';

    const PAGES_SITEMAP = self::PAGES.'.sitemap';

    const PAGES_SEARCH = self::PAGES.'.search';

    const PAGES_EDIT = self::PAGES.'.edit';

    const CONTENT = 'content';

    const CONTENT_BOXES = self::CONTENT.'.boxes';

    const CONTENT_FILES = self::CONTENT.'.files';

    const CONTENT_FILES_BROWSER = self::CONTENT_FILES.'.browser';

    const CONTENT_ALIASES = self::CONTENT.'.aliases';

    const CONTENT_TAGS = self::CONTENT.'.tags';

    const CONTENT_IMAGES = self::CONTENT.'.images';

    const EXTENSIONS = 'extension';

    const EXTENSIONS_IMAGE = self::EXTENSIONS.'.image';

    const ADMINISTRATION = 'administration';

    const ADMINISTRATION_USERS = self::ADMINISTRATION.'.users';

    const ADMINISTRATION_VIEWS = self::ADMINISTRATION.'.views';

    const ADMINISTRATION_PLUGINS = self::ADMINISTRATION.'.plugins';

    const ADMINISTRATION_THEMES = self::ADMINISTRATION.'.themes';

    const ADMINISTRATION_SETTINGS = self::ADMINISTRATION.'.settings';

    const ADMINISTRATION_PROTOCOL = self::ADMINISTRATION.'.protocol';

    const ADMINISTRATION_PROTOCOL_LOGIN = self::ADMINISTRATION_PROTOCOL.'.login';

    const ADMINISTRATION_PHRASES = self::ADMINISTRATION.'.phrases';

    const ADMINISTRATION_CRONJOBS = self::ADMINISTRATION.'.cronjobs';

    const ADMINISTRATION_LINK_TYPES = self::ADMINISTRATION.'.link-types';

    const ADMINISTRATION_MIME_TYPES = self::ADMINISTRATION.'.mime-types';

    const ADMINISTRATION_SPAM_FILTER = self::ADMINISTRATION.'.spam-filter';

    const ADMINISTRATION_ICONS = self::ADMINISTRATION.'.icons';

    const HELP = 'help';

    const XML_API = 'xml-api';

    const LOGOUT = 'logout';

    const INSTALLER = 'install';

    public function __invoke(\Papaya\Administration\UI $ui, Route\Address $path, $level = 0);
  }
}

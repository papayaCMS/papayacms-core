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
    const OVERVIEW = 'index.php';

    const MESSAGES = 'msgbox.php';

    const PAGES_SITEMAP = 'tree.php';

    const PAGES_SEARCH = 'search.php';

    const PAGES_EDIT = 'topic.php';

    const CONTENT = 'content';

    const CONTENT_BOXES = self::CONTENT.'.boxes';

    const CONTENT_FILES = self::CONTENT.'.files';

    const CONTENT_ALIASES = self::CONTENT.'.alias';

    const CONTENT_TAGS = 'tags.php';

    const CONTENT_IMAGES = self::CONTENT.'.images';

    const EXTENSIONS = 'extension';

    const ADMINISTRATION = 'administration';

    const ADMINISTRATION_USERS = self::ADMINISTRATION.'.users';

    const ADMINISTRATION_VIEWS = 'views.php';

    const ADMINISTRATION_PLUGINS = 'modules.php';

    const ADMINISTRATION_THEMES = 'themes.php';

    const ADMINISTRATION_SETTINGS = 'options.php';

    const ADMINISTRATION_PROTOCOL = self::ADMINISTRATION.'.protocol';

    const ADMINISTRATION_PROTOCOL_LOGIN = self::ADMINISTRATION_PROTOCOL.'.login';

    const ADMINISTRATION_PHRASES = 'phrases.php';

    const ADMINISTRATION_CRONJOBS = self::ADMINISTRATION.'.cronjobs';

    const ADMINISTRATION_LINK_TYPES = self::ADMINISTRATION.'.link-types';

    const HELP = 'help';

    public function __invoke(\Papaya\Administration\UI $ui, Route\Address $path, $level = 0);
  }
}

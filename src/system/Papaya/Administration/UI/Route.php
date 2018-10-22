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

    const CONTENT_BOXES = 'boxes.php';

    const CONTENT_FILES = 'mediadb.php';

    const CONTENT_ALIASES = 'alias.php';

    const CONTENT_TAGS = 'tags.php';

    const CONTENT_IMAGES = 'imggen.php';

    const APPLICATIONS = 'module.php';

    const ADMINISTRATION_USERS = 'auth.php';

    const ADMINISTRATION_VIEWS = 'views.php';

    const ADMINISTRATION_PLUGINS = 'modules.php';

    const ADMINISTRATION_THEMES = 'themes.php';

    const ADMINISTRATION_SETTINGS = 'options.php';

    const ADMINISTRATION_PROTOCOL = 'log.php';

    const ADMINISTRATION_PHRASES = 'phrases.php';

    public function __invoke(array $path);
  }
}

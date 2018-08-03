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

namespace Papaya\Application;

/**
 * A pseudoclass extending the \PapayaApplication service locator that
 * allows to declare the profiles as properties
 *
 * @property \Papaya\Database\Manager $database
 * @property \PapayaUiImages images
 * @property \Papaya\Content\Languages $languages
 * @property \Papaya\Message\Manager $messages
 * @property \Papaya\Configuration\Cms $options
 * @property \Papaya\Plugin\Loader $plugins
 * @property \Papaya\Profiler $profiler
 * @property \Papaya\Request $request
 * @property \Papaya\Response $response$response
 * @property \Papaya\Session $session
 * @property \base_surfer $surfer
 * @property \base_auth $administrationUser
 * @property \Papaya\Administration\Languages\Selector $administrationLanguage
 * @property \Papaya\Ui\Reference\Factory $references
 * @property \Papaya\Ui\Reference\Page\Factory $pageReferences
 * @property \Papaya\Phrases $phrases
 */
abstract class Cms extends \Papaya\Application {

}

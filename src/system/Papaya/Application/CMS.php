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
 * A pseudoclass extending the \Papaya\Application service locator that
 * allows to declare the profiles as properties
 *
 * @property \Papaya\Database\Manager $database
 * @property \papaya_page|NULL $front
 * @property \Papaya\UI\Images images
 * @property \Papaya\Content\Languages $languages
 * @property \Papaya\Message\Manager $messages
 * @property \Papaya\Configuration\CMS $options
 * @property \Papaya\Plugin\Loader $plugins
 * @property \Papaya\Profiler $profiler
 * @property \Papaya\Request $request
 * @property \Papaya\Response $response
 * @property \Papaya\Session $session
 * @property \base_surfer $surfer
 * @property \Papaya\UI\Reference\Factory $references
 * @property \Papaya\UI\Reference\Page\Factory $pageReferences
 * @property \base_auth $administrationUser
 * @property \Papaya\Administration\Languages\Selector $administrationLanguage
 * @property \Papaya\Phrases $administrationPhrases
 * @property \Papaya\Administration\RichText\Toggle $administrationRichText
 */
abstract class CMS extends \Papaya\Application {
}

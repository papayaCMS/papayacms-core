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

namespace Papaya\CMS\Administration {

  use Papaya\CMS\Administration\LinkTypes\Editor as LinkTypeEditor;
  use Papaya\CMS\Administration\Media\MediaFilesPage;
  use Papaya\CMS\Administration\Media\MimeTypes\Editor as MimeTypesEditor;
  use Papaya\CMS\Administration\Protocol\ProtocolPage;
  use Papaya\CMS\Administration\Settings\SettingsPage;
  use Papaya\CMS\Administration\UI\Route\FeatureFlag;
  use Papaya\Application;
  use Papaya\CMS\CMSConfiguration;
  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Router\Path as RouterAddress;
  use Papaya\Router\Route;
  use Papaya\Template;

  class UI extends Router {
    public const OVERVIEW = 'overview';

    public const MESSAGES = 'messages';

    public const MESSAGES_TASKS = self::MESSAGES.'.tasks';

    public const PAGES = 'pages';

    public const PAGES_SITEMAP = self::PAGES.'.sitemap';

    public const PAGES_SEARCH = self::PAGES.'.search';

    public const PAGES_EDIT = self::PAGES.'.edit';

    public const CONTENT = 'content';

    public const CONTENT_BOXES = self::CONTENT.'.boxes';

    public const CONTENT_FILES = self::CONTENT.'.files';

    public const CONTENT_FILES_BROWSER = self::CONTENT_FILES.'.browser';

    public const CONTENT_ALIASES = self::CONTENT.'.aliases';

    public const CONTENT_TAGS = self::CONTENT.'.tags';

    public const CONTENT_IMAGES = self::CONTENT.'.images';

    public const EXTENSIONS = 'extension';

    public const EXTENSIONS_IMAGE = self::EXTENSIONS.'.image';

    public const ADMINISTRATION = 'administration';

    public const ADMINISTRATION_USERS = self::ADMINISTRATION.'.users';

    public const ADMINISTRATION_VIEWS = self::ADMINISTRATION.'.views';

    public const ADMINISTRATION_PLUGINS = self::ADMINISTRATION.'.plugins';

    public const ADMINISTRATION_THEMES = self::ADMINISTRATION.'.themes';

    public const ADMINISTRATION_SETTINGS = self::ADMINISTRATION.'.settings';

    public const ADMINISTRATION_PROTOCOL = self::ADMINISTRATION.'.protocol';

    public const ADMINISTRATION_PROTOCOL_LOGIN = self::ADMINISTRATION_PROTOCOL.'.login';

    public const ADMINISTRATION_PHRASES = self::ADMINISTRATION.'.phrases';

    public const ADMINISTRATION_CRONJOBS = self::ADMINISTRATION.'.cronjobs';

    public const ADMINISTRATION_LINK_TYPES = self::ADMINISTRATION.'.link-types';

    public const ADMINISTRATION_MIME_TYPES = self::ADMINISTRATION.'.mime-types';

    public const ADMINISTRATION_SPAM_FILTER = self::ADMINISTRATION.'.spam-filter';

    public const ADMINISTRATION_ICONS = self::ADMINISTRATION.'.icons';

    public const HELP = 'help';

    public const XML_API = 'xml-api';

    public const LOGOUT = 'logout';

    public const INSTALLER = 'install';

    public const POPUP = 'popup';

    public const POPUP_COLOR = self::POPUP.'/color';

    public const POPUP_GOOGLE_MAPS = self::POPUP.'/googlemaps';

    public const POPUP_IMAGE = self::POPUP.'/image';

    public const POPUP_PAGE = self::POPUP.'/page';

    public const POPUP_MEDIA_BROWSER_HEADER = self::POPUP.'/media-header';

    public const POPUP_MEDIA_BROWSER_FOOTER = self::POPUP.'/media-footer';

    public const POPUP_MEDIA_BROWSER_IMAGES = self::POPUP.'/media-images';

    public const POPUP_MEDIA_BROWSER_FILES = self::POPUP.'/media-files';

    public const STYLES = 'styles';

    public const STYLES_CSS = self::STYLES.'/css';

    public const STYLES_CSS_POPUP = self::STYLES_CSS.'.popup';

    public const STYLES_CSS_RICHTEXT = self::STYLES_CSS.'.richtext';

    public const STYLES_JAVASCRIPT = self::STYLES.'/js';

    public const SCRIPTS = 'scripts';

    public const SCRIPTS_RTE = 'script';

    public const SCRIPTS_TINYMCE = self::SCRIPTS_RTE.'/tiny_mce3';

    public const SCRIPTS_TINYMCE_FILES = self::SCRIPTS_TINYMCE.'/files';

    public const SCRIPTS_TINYMCE_POPUP = self::SCRIPTS_TINYMCE.'/plugins/papaya';

    public const SCRIPTS_TINYMCE_POPUP_LINK = self::SCRIPTS_TINYMCE_POPUP.'/link';

    public const SCRIPTS_TINYMCE_POPUP_IMAGE = self::SCRIPTS_TINYMCE_POPUP.'/dynamic-image';

    public const SCRIPTS_TINYMCE_POPUP_PLUGIN = self::SCRIPTS_TINYMCE_POPUP.'/plugin';

    public const ICON = 'icon';

    public const ICON_MIMETYPE = self::ICON.'.mimetypes';

    public const ICON_LANGUAGE = './pics/language';

    /**
     * @var string
     */
    private $_path;

    /**
     * @var Template
     */
    private $_template;

    /**
     * @var callable
     */
    private $_route;

    private $_address;

    public function __construct($path, Application $application) {
      $this->_path = \str_replace(DIRECTORY_SEPARATOR, '/', $path);
      parent::__construct($application, NULL);
    }

    /**
     * @return string Local path to administration directory
     */
    public function getLocalPath() {
      return $this->_path;
    }

    /**
     * Initialize application and options and execute routes depending on the URL.
     *
     * Possible return values for routes:
     *   \Papaya\Response - returned from this method
     *   TRUE - request was handled, do not execute other routes
     *   NULL - not handled, continue route execution
     *   callable - new route, execute
     *
     * @return null|Response
     */
    public function execute() {
      $application = $this->papaya();
      $application->messages->setUp($application->options, $this->template());
      if ($application->options->get(\Papaya\CMS\CMSConfiguration::LOG_RUNTIME_REQUEST, FALSE)) {
        \Papaya\Request\Log::getInstance();
      }
      $application->request->isAdministration = TRUE;
      $application->session->isAdministration(TRUE);
      if ($redirect = $application->session->activate(TRUE)) {
        $redirect->send(TRUE);
      }
      $application->pageReferences->setPreview(TRUE);
      return parent::execute();
    }

    public function getRouteContext() {
      return $this->address();
    }

    /**
     * @param RouterAddress|null $address
     * @return RouterAddress
     */
    public function address(RouterAddress $address = NULL) {
      if (NULL !== $address) {
        $this->_address = $address;
      } elseif (NULL === $this->_address) {
        $this->_address = new UI\Path(
          $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::PATH_ADMIN, '')
        );
      }
      return $this->_address;
    }

    /**
     * @return callable|Route
     */
    public function createRoute() {
      $template = $this->template();
      $images = $this->papaya()->images;
      $localPath = $this->getLocalPath();
      $cacheTime = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::CACHE_THEMES, FALSE)
        ? $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::CACHE_TIME_THEMES, 0) : 0;
      return new Route\Group(
      // logout and layout files need to work without login/authentication
        new Route\PathChoice(
          [
            self::LOGOUT => new UI\Route\LogOut(),
            self::STYLES => function (self $ui) use ($localPath, $cacheTime) {
              $this->papaya()->options->loadAndDefine();
              $stylePath = $localPath.'/styles';
              $themePath = $stylePath.'/themes';
              $themeName = empty($_GET['theme'])
                ? $ui->papaya()->options->get(\Papaya\CMS\CMSConfiguration::UI_THEME, '')
                : $_GET['theme'];
              return new Route\Gzip(
                new UI\Route\Cache(
                  new Route\PathChoice(
                    [
                      self::STYLES_CSS => new Route\PathChoice(
                        [
                          self::STYLES_CSS => new Route\CSS($stylePath.'/main.css', $themeName, $themePath),
                          self::STYLES_CSS_POPUP => new Route\CSS($stylePath.'/popup.css', $themeName, $themePath),
                          self::STYLES_CSS_RICHTEXT => new Route\CSS($stylePath.'/richtext.css', $themeName, $themePath),
                        ]
                      ),
                      self::STYLES_JAVASCRIPT => new Route\JavaScript(
                        [$stylePath.'/functions.js', $stylePath.'/lightbox.js', $stylePath.'/richtext-toggle.js']
                      ),
                    ],
                    NULL,
                    0,
                    1
                  ),
                  $themeName,
                  $cacheTime,
                  UI\Route\Cache::CACHE_PUBLIC
                )
              );
            },
            self::SCRIPTS => function () use ($localPath, $cacheTime) {
              $this->papaya()->options->loadAndDefine();
              $files = isset($_GET['files']) ? \explode(',', $_GET['files']) : [];
              $files = \array_map(
                function ($file) use ($localPath) {
                  return $localPath.'/script/'.$file;
                },
                \array_filter(
                  $files,
                  function ($file) {
                    return \preg_match('(^[\w.-]+(/[\w.-]+)*\.js$)', $file);
                  }
                )
              );
              return new UI\Route\Cache(
                new Route\JavaScript($files),
                $files,
                $cacheTime,
                UI\Route\Cache::CACHE_PUBLIC
              );
            },
            self::SCRIPTS_RTE => new Route\PathChoice(
              [
                self::SCRIPTS_TINYMCE => new Route\PathChoice(
                  [
                    self::SCRIPTS_TINYMCE_FILES => new UI\Route\TinyMCE(),
                  ]
                ),
              ]
            ),
            self::ICON => new Route\Gzip(
              new UI\Route\Cache(
                new UI\Route\Icon($localPath.'/pics/icons'),
                isset($_GET['size']) && \in_array((int)$_GET['size'], UI\Route\Icon::SIZES, TRUE)
                  ? (int)$_GET['size'] : 16,
                $cacheTime,
                UI\Route\Cache::CACHE_PUBLIC
              )
            ),
          ]
        ),
        // enforce https (if configured)
        new UI\Route\Templated\SecureProtocol($template),
        // installer before authentication
        new Route\PathChoice(
          [
            self::INSTALLER => new UI\Route\Templated\Installer($template),
          ]
        ),
        // redirect broken installs
        new UI\Route\ValidateInstall(),
        // Authentication needed
        new UI\Route\Templated\Authenticated(
          $template,
          new Route\Group(
          // validate options and add warnings
            new UI\Route\ValidateOptions(),
            new Route\PathChoice(
              [
                // General
                self::OVERVIEW => new UI\Route\Templated\Page(
                  $template,
                  $images['places-home'] ?? '',
                  ['General', 'Overview'],
                  \papaya_overview::class
                ),
                self::MESSAGES => new Route\PathChoice(
                  [
                    self::MESSAGES => new UI\Route\Templated\Page(
                      $template,
                      $images['status-mail-open'] ?? '',
                      ['General', 'Messages'],
                      \papaya_messages::class
                    ),
                    self::MESSAGES_TASKS => new UI\Route\Templated\Page(
                      $template, $images['items-task'] ?? '', ['General', 'Messages', 'Tasks'], \papaya_todo::class
                    ),
                  ]
                ),

                // Pages
                self::PAGES => new Route\PathChoice(
                  [
                    self::PAGES_SITEMAP => new UI\Route\Templated\Page(
                      $template, $images['categories-sitemap'] ?? '', ['Pages', 'Sitemap'], \papaya_topic_tree::class, Permissions::PAGE_MANAGE
                    ),
                    self::PAGES_SEARCH => new UI\Route\Templated\Page(
                      $template, $images['actions-search'] ?? '', ['Pages', 'Search'], \papaya_overview_search::class, Permissions::PAGE_SEARCH
                    ),
                    self::PAGES_EDIT => new UI\Route\Templated\Page(
                      $template, $images['items-page'] ?? '', 'Pages', \papaya_topic::class, Permissions::PAGE_MANAGE
                    ),
                  ]
                ),

                // Additional Content
                self::CONTENT => new Route\PathChoice(
                  [
                    self::CONTENT_BOXES => new UI\Route\Templated\Page(
                      $template, $images['items-box'] ?? '', ['Content', 'Boxes'], \papaya_boxes::class, Permissions::BOX_MANAGE
                    ),
                    self::CONTENT_FILES => new Route\PathChoice(
                      [
                        self::CONTENT_FILES => new UI\Route\Templated\Page(
                          $template, $images['items-folder'] ?? '', ['Content', 'Files'], \papaya_mediadb::class, Permissions::FILE_MANAGE
                        ),
                        self::CONTENT_FILES_BROWSER => new UI\Route\Templated\Page(
                          $template, $images['items-folder'] ?? '', ['Content', 'Files'],
                          \papaya_mediadb_browser::class, Permissions::FILE_BROWSE

                        ),
                        self::CONTENT_FILES.'.refactor' => new FeatureFlag(
                          CMSConfiguration::FEATURE_MEDIA_DATABASE_2,
                          new UI\Route\Templated\Page(
                            $template, $images['items-folder'] ?? '', ['Content', 'Files'], MediaFilesPage::class,
                            Permissions::FILE_MANAGE
                          )
                        ),
                      ]
                    ),
                    self::CONTENT_IMAGES => new UI\Route\Templated\Page(
                      $template, $images['items-graphic'] ?? '', ['Content', 'Dynamic Images'], \papaya_imagegenerator::class, Permissions::IMAGE_GENERATOR
                    ),
                    self::CONTENT_ALIASES => new UI\Route\Templated\Page(
                      $template, $images['items-alias'] ?? '', ['Content', 'Alias'], \papaya_alias_tree::class, Permissions::ALIAS_MANAGE
                    ),
                    self::CONTENT_TAGS => new UI\Route\Templated\Page(
                      $template, $images['items-tag'] ?? '', ['Content', 'Tags'], \papaya_tags::class, Permissions::TAG_MANAGE
                    ),
                  ]
                ),
                // Extensions/Applications
                self::EXTENSIONS => new UI\Route\Templated\Extensions(
                  $template, $images['categories-applications'] ?? '', 'Applications'
                ),
                // Administration
                self::ADMINISTRATION => new Route\PathChoice(
                  [
                    self::ADMINISTRATION_USERS => new UI\Route\Templated\Page(
                      $template, $images['items-user-group'] ?? '', ['Administration', 'Users'], \papaya_user::class, Permissions::USER_MANAGE
                    ),
                    self::ADMINISTRATION_VIEWS => new UI\Route\Templated\Page(
                      $template, $images['items-view'] ?? '', ['Administration', 'Views'], \base_viewlist::class, Permissions::VIEW_MANAGE
                    ),
                    self::ADMINISTRATION_PLUGINS => new UI\Route\Templated\Page(
                      $template, $images['items-plugin'] ?? '', ['Administration', 'Plugins / Modules'], \papaya_modulemanager::class, Permissions::MODULE_MANAGE
                    ),
                    self::ADMINISTRATION_THEMES => new UI\Route\Templated\Page(
                      $template, $images['items-theme'] ?? '', ['Administration', 'Themes', 'Skins'], Theme\Editor::class, Permissions::SYSTEM_THEME_SKIN_MANAGE
                    ),
                    self::ADMINISTRATION_PROTOCOL => new Route\PathChoice(
                      [
                        self::ADMINISTRATION_PROTOCOL => new UI\Route\Templated\Page(
                          $template, $images['categories-protocol'] ?? '', ['Administration', 'Protocol'], ProtocolPage::class, Permissions::SYSTEM_PROTOCOL
                        ),
                        self::ADMINISTRATION_PROTOCOL_LOGIN => new UI\Route\Templated\Page(
                          $template, $images['categories-protocol'] ?? '', ['Administration', 'Protocol', 'Login'], \papaya_auth_secure::class, Permissions::SYSTEM_PROTOCOL
                        ),
                      ]
                    ),
                    self::ADMINISTRATION_SETTINGS => new UI\Route\Templated\Page(
                      $template, $images['items-option'] ?? '', ['Administration', 'Settings'], SettingsPage::class, Permissions::SYSTEM_SETTINGS
                    ),
                    self::ADMINISTRATION_CRONJOBS => new UI\Route\Templated\Page(
                      $template, $images['items-cronjob'] ?? '', ['Administration', 'Settings', 'Cronjobs'], \base_cronjobs::class, Permissions::SYSTEM_CRONJOBS
                    ),
                    self::ADMINISTRATION_LINK_TYPES => new UI\Route\Templated\Page(
                      $template, $images['items-link'] ?? '', ['Administration', 'Settings', 'Link types'], LinkTypeEditor::class, Permissions::SYSTEM_LINKTYPES_MANAGE
                    ),
                    self::ADMINISTRATION_MIME_TYPES => new UI\Route\Templated\Page(
                      $template, $images['items-option'] ?? '', ['Administration', 'Settings', 'Mime types'], MimeTypesEditor::class, Permissions::SYSTEM_MIMETYPES_MANAGE
                    ),
                    self::ADMINISTRATION_SPAM_FILTER => new UI\Route\Templated\Page(
                      $template, $images['items-option'] ?? '', ['Administration', 'Settings', 'Spam filter'], \papaya_spamfilter::class, Permissions::SYSTEM_SETTINGS
                    ),
                    self::ADMINISTRATION_ICONS => new UI\Route\Templated\Page(
                      $template, $images['items-option'] ?? '', ['Administration', 'Settings', 'Icons'], Settings\Icons\Page::class, Permissions::SYSTEM_SETTINGS
                    ),
                    self::ADMINISTRATION_PHRASES => new UI\Route\Templated\Page(
                      $template, $images['items-translation'] ?? '', ['Administration', 'Translations'], \base_languages::class, Permissions::SYSTEM_TRANSLATE
                    ),
                  ]
                ),
                // Help
                self::HELP => new UI\Route\Templated\Page(
                  $template, $images['categories-help'] ?? '', 'Help', \papaya_help::class
                ),
                // Popups
                self::POPUP => function () use ($cacheTime, $localPath) {
                  return new UI\Route\Cache(
                    new Route\PathChoice(
                      [
                        self::POPUP_COLOR => new UI\Route\Popup($localPath.'/popup/color.xsl'),
                        self::POPUP_GOOGLE_MAPS => new UI\Route\Popup($localPath.'/popup/googlemaps.xsl'),
                        self::POPUP_IMAGE => new UI\Route\Popup($localPath.'/popup/image.xsl'),
                        self::POPUP_PAGE => new UI\Route\Popup($localPath.'/popup/page.xsl'),
                        self::POPUP_MEDIA_BROWSER_HEADER => new UI\Route\Popup($localPath.'/popup/media-header.xsl'),
                        self::POPUP_MEDIA_BROWSER_FOOTER => new UI\Route\Popup($localPath.'/popup/media-footer.xsl'),
                        self::POPUP_MEDIA_BROWSER_FILES => new UI\Route\Popup($localPath.'/popup/media-files.xsl'),
                        self::POPUP_MEDIA_BROWSER_IMAGES => new UI\Route\Popup($localPath.'/popup/media-images.xsl'),
                      ],
                      NULL,
                      0,
                      1
                    ),
                    $this->papaya()->administrationLanguage->code,
                    $cacheTime
                  );
                },
                // TinyMCE popups
                self::SCRIPTS_RTE => function () use ($localPath) {
                  $pluginPath = $localPath.'/script/tiny_mce3/plugins/papaya';
                  return new Route\PathChoice(
                    [
                      self::SCRIPTS_TINYMCE_POPUP_LINK => new UI\Route\Popup($pluginPath.'/link.xsl'),
                      self::SCRIPTS_TINYMCE_POPUP_IMAGE => new UI\Route\Popup(
                        $pluginPath.'/dynamic-image.xsl',
                        function (\Papaya\XML\Element $popupNode) {
                          /** @var \Papaya\XML\Document $document */
                          $document = $popupNode->ownerDocument;
                          $document->registerNamespace('popup', $popupNode->namespaceURI);
                          /** @var \Papaya\XML\Element $parentNode */
                          $parentNode = $document->xpath()->evaluate('.//popup:image-generators[1]', $popupNode)[0];
                          if ($parentNode) {
                            $imgGenerator = new \papaya_imagegenerator();
                            $imgGenerator->loadImageConfs();
                            if (
                              \is_array($imgGenerator->imageConfs) &&
                              \count($imgGenerator->imageConfs) > 0
                            ) {
                              foreach ($imgGenerator->imageConfs as $image) {
                                $parentNode->appendElement(
                                  'popup:image',
                                  [
                                    'name' => $image['image_ident'],
                                    'title' => $image['image_title'],
                                  ]
                                );
                              }
                            }
                          }
                        }
                      ),
                      self::SCRIPTS_TINYMCE_POPUP_PLUGIN => new UI\Route\Popup(
                        $pluginPath.'/plugin.xsl',
                        function (\Papaya\XML\Element $popupNode) {
                          /** @var \Papaya\XML\Document $document */
                          $document = $popupNode->ownerDocument;
                          $document->registerNamespace('popup', $popupNode->namespaceURI);
                          foreach ($document->xpath()->evaluate('.//popup:plugins', $popupNode) as $parentNode) {
                            /** @var \Papaya\XML\Element $parentNode */
                            if ($parentNode) {
                              $plugins = $this->papaya()->plugins->plugins()->withType(
                                $parentNode->getAttribute('type') ?: \Papaya\CMS\Plugin\Types::PARSER
                              );
                              foreach ($plugins as $plugin) {
                                $parentNode->appendElement(
                                  'popup:plugin',
                                  [
                                    'guid' => $plugin['guid'],
                                    'title' => $plugin['title'],
                                  ]
                                );
                              }
                            }
                          }
                        }
                      ),
                    ],
                    NULL,
                    0,
                    4
                  );
                },
                // XML
                self::XML_API => function () {
                  $rpcCall = new \papaya_rpc();
                  $rpcCall->initialize();
                  $rpcCall->execute();
                  $response = new Response();
                  $response->setContentType('application/xml');
                  $response->content(new Response\Content\Text($rpcCall->getXML()));
                  if ($this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::LOG_RUNTIME_REQUEST, FALSE)) {
                    \Papaya\Request\Log::getInstance()->emit();
                    $this->papaya()->database->close();
                  }
                  return $response;
                },
              ],
              self::OVERVIEW
            )
          )
        ),
        new Route\Error('Unknown route!', 404)
      );
    }

    /**
     * @param Template|null $template
     * @return Template
     */
    public function template(Template $template = NULL) {
      if (NULL !== $template) {
        $this->_template = $template;
      } elseif (NULL === $this->_template) {
        $this->_template = new Template\XSLT(
          $this->getLocalPath().'/template/style.xsl'
        );
      }
      return $this->_template;
    }
  }
}

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
namespace Papaya\Administration {

  use Papaya\Administration\LinkTypes\Editor as LinkTypeEditor;
  use Papaya\Application;
  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Router\Address as RouterAddress;
  use Papaya\Router\Route;
  use Papaya\Template;

  class UI extends Router {
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

    const POPUP = 'popup';

    const POPUP_COLOR = self::POPUP.'/color';

    const POPUP_GOOGLE_MAPS = self::POPUP.'/googlemaps';

    const POPUP_IMAGE = self::POPUP.'/image';

    const POPUP_PAGE = self::POPUP.'/page';

    const POPUP_MEDIA_BROWSER_HEADER = self::POPUP.'/media-header';

    const POPUP_MEDIA_BROWSER_FOOTER = self::POPUP.'/media-footer';

    const POPUP_MEDIA_BROWSER_IMAGES = self::POPUP.'/media-images';

    const POPUP_MEDIA_BROWSER_FILES = self::POPUP.'/media-files';

    const STYLES = 'styles';

    const STYLES_CSS = self::STYLES.'/css';

    const STYLES_CSS_POPUP = self::STYLES_CSS.'.popup';

    const STYLES_CSS_RICHTEXT = self::STYLES_CSS.'.richtext';

    const STYLES_JAVASCRIPT = self::STYLES.'/js';

    const SCRIPTS = 'scripts';

    const SCRIPTS_RTE = 'script';

    const SCRIPTS_TINYMCE = self::SCRIPTS_RTE.'/tiny_mce3';

    const SCRIPTS_TINYMCE_FILES = self::SCRIPTS_TINYMCE.'/files';

    const SCRIPTS_TINYMCE_POPUP = self::SCRIPTS_TINYMCE.'/plugins/papaya';

    const SCRIPTS_TINYMCE_POPUP_LINK = self::SCRIPTS_TINYMCE_POPUP.'/link';

    const SCRIPTS_TINYMCE_POPUP_IMAGE = self::SCRIPTS_TINYMCE_POPUP.'/dynamic-image';

    const SCRIPTS_TINYMCE_POPUP_PLUGIN = self::SCRIPTS_TINYMCE_POPUP.'/plugin';

    const ICON = 'icon';

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
      parent::__construct($application, null);
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
      if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
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
        $this->_address = new UI\Address(
          $this->papaya()->options->get('PAPAYA_PATH_ADMIN', '')
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
      $cacheTime = $this->papaya()->options->get('PAPAYA_CACHE_THEMES', FALSE)
        ? $this->papaya()->options->get('PAPAYA_CACHE_TIME_THEMES', 0) : 0;
      return new Route\Group(
        // logout and layout files need to work without login/authentication
        new Route\Choice(
          [
            self::LOGOUT => new UI\Route\LogOut(),
            self::STYLES => function(self $ui) use ($localPath, $cacheTime) {
              $stylePath = $localPath.'/styles';
              $themePath = $stylePath.'/themes';
              $themeName = empty($_GET['theme'])
                ? $ui->papaya()->options->get('PAPAYA_UI_THEME', '')
                : $_GET['theme'];
              return new Route\Gzip(
                new UI\Route\Cache(
                  new Route\Choice(
                    [
                      self::STYLES_CSS => new Route\Choice(
                        [
                          self::STYLES_CSS => new Route\CSS($stylePath.'/main.css', $themeName, $themePath),
                          self::STYLES_CSS_POPUP => new Route\CSS($stylePath.'/popup.css', $themeName, $themePath),
                          self::STYLES_CSS_RICHTEXT => new Route\CSS($stylePath.'/richtext.css', $themeName, $themePath)
                        ]
                      ),
                      self::STYLES_JAVASCRIPT => new Route\JavaScript(
                        [$stylePath.'/functions.js', $stylePath.'/lightbox.js', $stylePath.'/richtext-toggle.js']
                      )
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
            self::SCRIPTS => function() use ($localPath, $cacheTime) {
              $files = isset($_GET['files']) ? \explode(',', $_GET['files']) : [];
              $files = \array_map(
                function($file) use ($localPath) {
                  return $localPath.'/script/'.$file;
                },
                \array_filter(
                  $files,
                  function($file) {
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
            self::SCRIPTS_RTE => new Route\Choice(
              [
                self::SCRIPTS_TINYMCE => new Route\Choice(
                  [
                    self::SCRIPTS_TINYMCE_FILES => new UI\Route\TinyMCE()
                  ]
                )
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
            )
          ]
        ),
        // enforce https (if configured)
        new UI\Route\Templated\SecureProtocol($template),
        // installer before authentication
        new Route\Choice(
          [
            self::INSTALLER => new UI\Route\Templated\Installer($template)
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
            new Route\Choice(
              [
                // General
                self::OVERVIEW => new UI\Route\Templated\Page(
                  $template, $images['places-home'], ['General', 'Overview'], \papaya_overview::class
                ),
                self::MESSAGES => new Route\Choice(
                  [
                    self::MESSAGES => new UI\Route\Templated\Page(
                      $template, $images['status-mail-open'], ['General', 'Messages'], \papaya_messages::class
                    ),
                    self::MESSAGES_TASKS => new UI\Route\Templated\Page(
                      $template, $images['items-task'], ['General', 'Messages', 'Tasks'], \papaya_todo::class
                    ),
                  ]
                ),

                // Pages
                self::PAGES => new Route\Choice(
                  [
                    self::PAGES_SITEMAP => new UI\Route\Templated\Page(
                      $template, $images['categories-sitemap'], ['Pages', 'Sitemap'], \papaya_topic_tree::class, Permissions::PAGE_MANAGE
                    ),
                    self::PAGES_SEARCH => new UI\Route\Templated\Page(
                      $template, $images['actions-search'], ['Pages', 'Search'], \papaya_overview_search::class, Permissions::PAGE_SEARCH
                    ),
                    self::PAGES_EDIT => new UI\Route\Templated\Page(
                      $template, $images['items-page'], 'Pages', \papaya_topic::class, Permissions::PAGE_MANAGE
                    )
                  ]
                ),

                // Additional Content
                self::CONTENT => new Route\Choice(
                  [
                    self::CONTENT_BOXES => new UI\Route\Templated\Page(
                      $template, $images['items-box'], ['Content', 'Boxes'], \papaya_boxes::class, Permissions::BOX_MANAGE
                    ),
                    self::CONTENT_FILES => new Route\Choice(
                      [
                        self::CONTENT_FILES => new UI\Route\Templated\Page(
                          $template, $images['items-folder'], ['Content', 'Files'], \papaya_mediadb::class, Permissions::FILE_MANAGE
                        ),
                        self::CONTENT_FILES_BROWSER => new UI\Route\Templated\Page(
                          $template, $images['items-folder'], ['Content', 'Files'], \papaya_mediadb_browser::class, Permissions::FILE_BROWSE
                        )
                      ]
                    ),
                    self::CONTENT_IMAGES => new UI\Route\Templated\Page(
                      $template, $images['items-graphic'], ['Content', 'Dynamic Images'], \papaya_imagegenerator::class, Permissions::IMAGE_GENERATOR
                    ),
                    self::CONTENT_ALIASES => new UI\Route\Templated\Page(
                      $template, $images['items-alias'], ['Content', 'Alias'], \papaya_alias_tree::class, Permissions::ALIAS_MANAGE
                    ),
                    self::CONTENT_TAGS => new UI\Route\Templated\Page(
                      $template, $images['items-tag'], ['Content', 'Tags'], \papaya_tags::class, Permissions::TAG_MANAGE
                    )
                  ]
                ),
                // Extensions/Applications
                self::EXTENSIONS => new UI\Route\Templated\Extensions(
                  $template, $images['categories-applications'], 'Applications'
                ),
                // Administration
                self::ADMINISTRATION => new Route\Choice(
                  [
                    self::ADMINISTRATION_USERS => new UI\Route\Templated\Page(
                      $template, $images['items-user-group'], ['Administration', 'Users'], \papaya_user::class, Permissions::USER_MANAGE
                    ),
                    self::ADMINISTRATION_VIEWS => new UI\Route\Templated\Page(
                      $template, $images['items-view'], ['Administration', 'Views'], \base_viewlist::class, Permissions::VIEW_MANAGE
                    ),
                    self::ADMINISTRATION_PLUGINS => new UI\Route\Templated\Page(
                      $template, $images['items-plugin'], ['Administration', 'Plugins / Modules'], \papaya_modulemanager::class, Permissions::MODULE_MANAGE
                    ),
                    self::ADMINISTRATION_THEMES => new UI\Route\Templated\Page(
                      $template, $images['items-theme'], ['Administration', 'Themes', 'Skins'], Theme\Editor::class, Permissions::SYSTEM_THEME_SKIN_MANAGE
                    ),
                    self::ADMINISTRATION_PROTOCOL => new Route\Choice(
                      [
                        self::ADMINISTRATION_PROTOCOL => new UI\Route\Templated\Page(
                          $template, $images['categories-protocol'], ['Administration', 'Protocol'], \papaya_log::class, Permissions::SYSTEM_PROTOCOL
                        ),
                        self::ADMINISTRATION_PROTOCOL_LOGIN => new UI\Route\Templated\Page(
                          $template, $images['categories-protocol'], ['Administration', 'Protocol', 'Login'], \papaya_auth_secure::class, Permissions::SYSTEM_PROTOCOL
                        )
                      ]
                    ),
                    self::ADMINISTRATION_SETTINGS => new UI\Route\Templated\Page(
                      $template, $images['items-option'], ['Administration', 'Settings'], \papaya_options::class, Permissions::SYSTEM_SETTINGS
                    ),
                    self::ADMINISTRATION_CRONJOBS => new UI\Route\Templated\Page(
                      $template, $images['items-cronjob'], ['Administration', 'Settings', 'Cronjobs'], \base_cronjobs::class, Permissions::SYSTEM_CRONJOBS
                    ),
                    self::ADMINISTRATION_LINK_TYPES => new UI\Route\Templated\Page(
                      $template, $images['items-link'], ['Administration', 'Settings', 'Link types'], LinkTypeEditor::class, Permissions::SYSTEM_LINKTYPES_MANAGE
                    ),
                    self::ADMINISTRATION_MIME_TYPES => new UI\Route\Templated\Page(
                      $template, $images['items-option'], ['Administration', 'Settings', 'Mime types'], \papaya_mediadb_mime::class, Permissions::SYSTEM_MIMETYPES_MANAGE
                    ),
                    self::ADMINISTRATION_SPAM_FILTER => new UI\Route\Templated\Page(
                      $template, $images['items-option'], ['Administration', 'Settings', 'Spam filter'], \papaya_spamfilter::class, Permissions::SYSTEM_SETTINGS
                    ),
                    self::ADMINISTRATION_ICONS => new UI\Route\Templated\Page(
                      $template, $images['items-option'], ['Administration', 'Settings', 'Icons'], Settings\Icons\Page::class, Permissions::SYSTEM_SETTINGS
                    ),
                    self::ADMINISTRATION_PHRASES => new UI\Route\Templated\Page(
                      $template, $images['items-translation'], ['Administration', 'Translations'], \base_languages::class, Permissions::SYSTEM_TRANSLATE
                    ),
                  ]
                ),
                // Help
                self::HELP => new UI\Route\Templated\Page(
                  $template, $images['categories-help'], 'Help', \papaya_help::class
                ),
                // Popups
                self::POPUP => function() use ($cacheTime, $localPath) {
                  return new UI\Route\Cache(
                    new Route\Choice(
                      [
                        self::POPUP_COLOR => new UI\Route\Popup($localPath.'/popup/color.xsl'),
                        self::POPUP_GOOGLE_MAPS => new UI\Route\Popup($localPath.'/popup/googlemaps.xsl'),
                        self::POPUP_IMAGE => new UI\Route\Popup($localPath.'/popup/image.xsl'),
                        self::POPUP_PAGE => new UI\Route\Popup($localPath.'/popup/page.xsl'),
                        self::POPUP_MEDIA_BROWSER_HEADER => new UI\Route\Popup($localPath.'/popup/media-header.xsl'),
                        self::POPUP_MEDIA_BROWSER_FOOTER => new UI\Route\Popup($localPath.'/popup/media-footer.xsl'),
                        self::POPUP_MEDIA_BROWSER_FILES => new UI\Route\Popup($localPath.'/popup/media-files.xsl'),
                        self::POPUP_MEDIA_BROWSER_IMAGES => new UI\Route\Popup($localPath.'/popup/media-images.xsl')
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
                self::SCRIPTS_RTE => function() use ($localPath) {
                  $pluginPath = $localPath.'/script/tiny_mce3/plugins/papaya';
                  return new Route\Choice(
                    [
                      self::SCRIPTS_TINYMCE_POPUP_LINK => new UI\Route\Popup($pluginPath.'/link.xsl'),
                      self::SCRIPTS_TINYMCE_POPUP_IMAGE => new UI\Route\Popup(
                        $pluginPath.'/dynamic-image.xsl',
                        function(\Papaya\XML\Element $popupNode) {
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
                                    'title' => $image['image_title']
                                  ]
                                );
                              }
                            }
                          }
                        }
                      ),
                      self::SCRIPTS_TINYMCE_POPUP_PLUGIN => new UI\Route\Popup(
                        $pluginPath.'/plugin.xsl',
                        function(\Papaya\XML\Element $popupNode) {
                          /** @var \Papaya\XML\Document $document */
                          $document = $popupNode->ownerDocument;
                          $document->registerNamespace('popup', $popupNode->namespaceURI);
                          foreach ($document->xpath()->evaluate('.//popup:plugins', $popupNode) as $parentNode) {
                            /** @var \Papaya\XML\Element $parentNode */
                            if ($parentNode) {
                              $plugins = $this->papaya()->plugins->plugins()->withType(
                                $parentNode->getAttribute('type') ?: \Papaya\Plugin\Types::PARSER
                              );
                              foreach ($plugins as $plugin) {
                                $parentNode->appendElement(
                                  'popup:plugin',
                                  [
                                    'guid' => $plugin['guid'],
                                    'title' => $plugin['title']
                                  ]
                                );
                              }
                            }
                          }
                        }
                      )
                    ],
                    NULL,
                    0,
                    4
                  );
                },
                // XML
                self::XML_API => function() {
                  $rpcCall = new \papaya_rpc();
                  $rpcCall->initialize();
                  $rpcCall->execute();
                  $response = new Response();
                  $response->setContentType('application/xml');
                  $response->content(new Response\Content\Text($rpcCall->getXML()));
                  if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
                    \Papaya\Request\Log::getInstance()->emit();
                    $this->papaya()->database->close();
                  }
                  return $response;
                }
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

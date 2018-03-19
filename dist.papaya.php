<?php
/*------------------------------------------------------------------------------
                             Database access
------------------------------------------------------------------------------*/

/**
* Database URI
*/
define('PAPAYA_DB_URI', '${papaya.database.uri}');

/**
* Database URI for Insert/Update/... if different
*/
define('PAPAYA_DB_URI_WRITE', NULL);

/**
* options table name (including prefix)
*/
define('PAPAYA_DB_TBL_OPTIONS', 'papaya_options');

/**
* papaya tables prefix
*/
define('PAPAYA_DB_TABLEPREFIX', 'papaya');

/**
* papaya can use persistent connections (be careful with that option)
*/
define('PAPAYA_DB_CONNECT_PERSISTENT', FALSE);



/*------------------------------------------------------------------------------
                      Maintenance / Technical problems
------------------------------------------------------------------------------*/

/**
* maintenance mode - show maintenance page
*/
define('PAPAYA_MAINTENANCE_MODE', FALSE);

/**
* maintenance page - (error/maintenance.html)
*/
define('PAPAYA_ERRORDOCUMENT_MAINTENANCE', '');

/**
* technical problems page - (error/503.html) - no framework / no database
*/
define('PAPAYA_ERRORDOCUMENT_503', '');



/*------------------------------------------------------------------------------
                                Security
------------------------------------------------------------------------------*/

/**
* disable custom http-headers like X-Generator and X-Papaya-Status
*/
define('PAPAYA_DISABLE_XHEADERS', FALSE);

/**
* all to force https handling with a X-Papaya-Https in request header
*/
define('PAPAYA_HEADER_HTTPS_TOKEN', '');



/*------------------------------------------------------------------------------
                                Session Handling
------------------------------------------------------------------------------*/

/**
* suffix added to the default session name "sid" to handle conflicts
* changes here will require a change in the rewrite rules
*/
define('PAPAYA_SESSION_NAME', '');



/*------------------------------------------------------------------------------
                                Development
------------------------------------------------------------------------------*/

/**
* development mode - show parse errors for base includes and some other stuff
*/
define('PAPAYA_DBG_DEVMODE', ${papaya.development.active});
/**
* development mode - show parse errors for base includes and some other stuff
*/
define('PAPAYA_DEBUG_LANGUAGE_PHRASES', FALSE);


/*------------------------------------------------------------------------------
                                Profiling
------------------------------------------------------------------------------*/

/**
* Activate profiler - only possible if xhprof is installed
*/
define('PAPAYA_PROFILER_ACTIVE', FALSE);

/**
* A divisor to define the probability the profiling is done for the current request.
*
*   0 = deactivates profiler (same a PAPAYA_PROFILER_ACTIVE = FALSE)
*   1 = always
*   2 - 99999 = 1/n probability
*/
define('PAPAYA_PROFILER_DIVISOR', 1);

/**
* Storage engine used to save the profiling data
*
*   file = files a a defined directory
*   xhgui = mysl database table for XH Gui
*/
define('PAPAYA_PROFILER_STORAGE', 'xhgui');

/**
* Directory for profiling file storage - default value is the xhprof.output_dir ini option.
*/
define('PAPAYA_PROFILER_STORAGE_DIRECTORY', NULL);

/**
* Database uri for storage (used by xhgui)
*/
define('PAPAYA_PROFILER_STORAGE_DATABASE', NULL);

/**
* Database table name for storage (used by xhgui)
*/
define('PAPAYA_PROFILER_STORAGE_DATABASE_TABLE', 'details');

/**
* A server identifier (used by xhgui)
*/
define('PAPAYA_PROFILER_SERVER_ID', 'dv1');

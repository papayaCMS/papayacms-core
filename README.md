# papaya CMS

[papaya CMS](https://github.com/papayaCMS/) Content Management System and web application framework<br/>
Copyright: 2002-2018 dimensional GmbH (www.dimensional.de)

[(see project website to find out more about papaya CMS)](http://www.papaya-cms.com/)

[![Build Status](https://travis-ci.org/papayaCMS/papayacms-core.svg)](https://travis-ci.org/papayaCMS/papayacms-core)
[![License](https://poser.pugx.org/papaya/cms-core/license.svg)](https://packagist.org/packages/papaya/cms-core)
[![Total Downloads](https://poser.pugx.org/papaya/cms-core/downloads.svg)](https://packagist.org/packages/papaya/cms-core)
[![Latest Stable Version](https://poser.pugx.org/papaya/cms-core/v/stable.svg)](https://packagist.org/packages/papaya/cms-core)
[![Latest Unstable Version](https://poser.pugx.org/papaya/cms-core/v/unstable.svg)](https://packagist.org/packages/papaya/cms-core)

While we are trying to give a useful overview with this readme file,
please refer to our website www.papaya-cms.com for further details.

If you got any questions that are not answered by the documentation,
please leave a comment in the public forum at
http://www.papaya-cms.com/forum

If you've stumbled upon a bug or if you have any proposals for
improvements, we'd love to hear from you - please either file a bug
at https://bugs.papaya-cms.com/ or even fork the project and submit
a pull request.

Here are some starting points that will hopefully make it a pleasure
for you the get in touch with the papaya CMS:

-----------------------------------------------------------------------

#### English

 * Developer Documentation (Wiki) = http://en.wiki.papaya-cms.com/wiki/Main_Page
 * API-Documentation = http://www.papaya-cms.com/doc/papaya-cms/en/source/nightly/

Unfortunately, the english documentation has by far not yet reached the coverage
that the german documentation has. So if you speak at least a little German,
please have a look at the German links as well:

#### German

 * Website = http://www.papaya-cms.com/
 * Anwender-Handbuch = http://documentation.papaya-cms.com/doku.php
 * Entwickler-Dokumentation (Wiki) = http://de.wiki.papaya-cms.com/wiki/Hauptseite
 * API-Dokumentation = http://www.papaya-cms.com/doc/papaya-cms/en/source/nightly/

-----------------------------------------------------------------------

 * General
 * Licence
 * System Requirements
 * Installation
    1. Creating new project
    2. Basic configuration
    3. Directories
    4. Setting permissions
 * Initialization and Configuration
    1. Initialize Database
    2. Configuration
    3. Users and Passwords
 * Troubleshooting
 * Appendix
    1. Installing papaya CMS in a subdirectory
    2. Rewrite Rules in httpd.conf
    3. Apache mod_vhost_alias
    4. MySQL >= 4.1 and character sets


## General

Thank you for downloading papaya CMS and testing/using it. Usage is
free, under the conditions described in the GNU General Public
Licence, version 2 (GPL V2).

papaya CMS is an Open Source CMS aiming primarily at large-scale
websites and complex web applications. It does not use any proprietary
templating- or scripting-languages but is entirely based upon open standards
(e.g. PHP, MySQL/PostgreSQL, XSL/XSLT etc.).

Up-to-date information can be obtained from
[http://www.papaya-cms.com].


## Licence

papaya CMS is subject to the GNU General Public Licence, version 2
(GPL V2). See gpl.txt for the complete text of the GPL.

See credits.txt for a list of other open source software included in
this release of papaya CMS.

## System Requirements

### Server:
 * PHP >= 5.6
    * XML (ext/xml)
    * XSLT (ext/xsl)
    * Database extension (ext/sqlite3, ext/mysql, ext/mysqli or ext/pgsql)
    * Sessions (ext/session)
    * PCRE (ext/pcre)
    * GD (ext/gd)
    
    
 * Webserver
    * PHP embedded webserver (for development)
    * Apache httpd 2.x
        * mod_rewrite
 * Database
    * SQLite 3
    * MySQL >= 4.1.x
    * PostgreSQL >= 8.0

### Client (for Administration):
 * Webbrowser (Firefox recommended)
    * JavaScript

### Client (for output with default templates):
  * Webbrowser
    * JavaScript (optional - for popups and flash)


##  Installation

The installation is using [Composer](http://getcomposer.org) and [Phing](http://www.phing.info).

### Creating new project

Call Composer to get the project skeleton.

```
composer create-project papaya/cms-project projectname
```

#### Quick Setup (for development) 

Call Phing in the project directory 

```
cd projectname
phing
```

Start the PHP builtin webserver 

```
php -S localhost:8080 -t ./htdocs server.php
```

Open the URL `http://localhost:8080/papaya` in you browser (make sure Javascript is active) and
follow the installation steps.

You can initialize the project directory as a git repository and push it to
a server. You can read more about this in the section "Installation and Configuration" further 
below.

### Basic configuration

Copy `dist.build.properties` to `build.properties` and change the
database uri option. `build.properties` will not be committed to Git, so any developer
can have its own local build configuration.

Here are two database options: `database.uri` is for the local installation, 
`dist.database.uri` is for the distribution/release.

The database address following this scheme:

* `protocol://user:password@hostname/database` or
*  `protocol://./path/file`

e.g.
* `mysql://web1:secret@localhost/usr_web1_1` or
* `sqlite3://./papaya.sqlite`
   
You should have received this information from your ISP or System
Administrator.

Calling Phing inside the project directory will trigger Composer to install
the dependencies and create the configuration file (`papaya.php`). An
existing `papaya.php` will not be overwritten.

To overwrite `papaya.php` call

```
phing config-regenerate
```


### Export project for distribution

The Phing build file contains targets to compile project builds for distribution.

Create a directory: 

```
phing export-directory
```

Create a tar gz archive: 

```
phing export-tgz
```

Create a zip archive: 

```
phing export-zip
```

## Directories

### htdocs/

The document root for the webserver. If you need to rename you need to
provide it in the composer.json.

```javascript
{
  "extra" : {
    "document-root" : "htdocs/"
  }
}
```

### htdocs/papaya/

Composer will install the Administration interface into this directory.

### htdocs/papaya-themes/

Directory for the papaya themes. A theme contains the css, js and layout images
for a project. All static resources that need to be delivered to the browser.

They can be installed by Composer or be part of the project.

### papaya-data/

This directory contains the file cache and media files. It needs to be
writeable the webserver.

### src/

The directory for project papaya modules, except installed by Composer.

### templates/

The default directory for XSLT template sets. It  can be changed using the
composer.json.

```javascript
{
  "extra" : {
    "papaya" : {
      "template-directory": "templates/"
    }
  }
}
```

They can be installed by Composer or be part of the project.

### Setting permissions

#### Setting Permissions for Windows (XP, 2003 server, and higher)

Write permission has to be granted to the webserver for the folder
"papaya-data". File permissions can be set by using Windows
Explorer and right-clicking on the folder. However, Windows
installations do not usually require permissions to be set.

#### Setting Permissions for Unix (linux, unix, BSD etc)

Write permission has to be granted to the webserver for the
directory "papaya-data". File permissions can be set by your FTP
client. Set the permissions for this directory to "0777".

|         | user | group | others |
|---------|------|-------|--------|
| Read    |   X  |   X   |    X   |
| Write   |   X  |   X   |    X   |
| Execute |   X  |   X   |    X   |

More restrictive permissions may be possible. Please ask your server
administrator.

papaya CMS is now installed. Continue on to the _Initialization and
Configuration_ section of this document.

##  Initialization and Configuration

### 1. Start install script

Open http://www.domain.tld/papaya/install.php with your webbrowser
(replace www.domain.tld with your own domain). The start page of the
installation script is displayed. The start page contains a couple of
links to the FAQ, the installation forum, the support page, and the
papaya website.

Click on "Next" to get to the next step.


### 2. Agree to license

The next step of the insall script will display a copy of the GPL. You
need to accept the license agreement in order to proceed. Do do so, click
on "Accept license".


### 3. Check system

In the following step of the installation, the script will check if your
system is compatible with papaya CMS and whether all needed extensions
are available. If this is the case, you can go on with the next step of the
installation. Do so by clicking on "Next".


### 4. Define PAPAYA_PATH_DATA and set up admin account

#### 4a. Set path for PAPAYA_PATH_DATA

Enter the path to the directory papaya-data for the option PAPAYA_PATH_DATA.
Please provide an absolute path.

#### 4b. Set up account for the administrator

Enter the givenname, surname, the email address, the login name as well as
the password. Click on "Save".


### 5. Set up the configuration table

In the next step of the install script, you are prompted to create the
configuration table. Click on "Create" to create the configuration table
and proceed with the installation.

NOTE - The prompt isn't displayed when the database connection hasn't
       been configured properly.


### 6. Initialize database

Once the configuration table is created, you will see a list of database
tables as well as the following menu.

 1. _Analyze database_

    Checks existing tables in the database.
    (disabled when none of the necessary tables exists)

 2. _Update database_

    Create missing tables and update existing tables.
    (disabled if no modifications are necessary)

 3. _Insert default data_

    Insert default values in selected tables.
    WARNING :: EXISTING DATA WILL BE DELETED FROM THE TABLE!
    (you can perform this operation multiple times)

 4. _Check options and modules_

    Check options, set default values and look for installed modules
    (you can perform this operation multiple times)

 5. _Go to admin interface_

    Opens the user admin interface.

Click on each option, one after one. The installation tool will
modify existing tables without deleting data. The tool can be reused
when you want to update your system, without losing your content.

Tables for additional modules (e.g. forum) can be installed later, via
the administration module.

The database for papaya CMS is now ininitialized, and papaya CMS is ready
for configuration. When you click on the link in step "5) Go to admin
interface", you will automatically be logged in in the papaya backend where
you can start configuring papaya CMS.


### 7. Configuration

#### 7a) Login after database initialization

In case you have interrupted the installation procedure for papaya CMS and
want to configure papaya CMS at a later time, you need to log into the backend
of papaya CMS:

  1. Open http://www.domain.tld/papaya/ with your webbrowser. Please replace
     www.domain.tld with your actual domain where you have installed papaya
     CMS.
  2. Log in using your username and password. You have entered a username and
     a password when you have configured the account for the default
     administrator and should have the account information already.

In case you have continued with the installation directly after the database
initialization, you will be logged in automatically.

#### 7b) Going on with the configuration

Klick on the button "Settings" in the menu group "Administration". The  system
settings section of papaya CMS is opened.

Important options:

  Files and Directories
    PAPAYA_PATH_DATA   - Path to data directory (papaya-data/)
    PAPAYA_PATH_WEB    - Path below webroot (/)

  Layout
    PAPAYA_LAYOUT_TEMPLATES - XSLT directory
    PAPAYA_LAYOUT_THEME     - directory containing CSS and layout
                              images

IMPORTANT - Sometimes, the option PAPAYA_PATH_DATA cannot be set during
            installation. You can recognize a failed setting if a value
            is displayed for this option, but the option is set between
            brackets. You will have to edit and save the option. After
            saving the option, the brackets will disappear.

Click on "Check paths" after setting the option PAPAYA_PATH_DATA.
The system checks the data path permissions and creates
necessary subdirectories for the media database if they don't exist.


### 8. Users and passwords

Click in the menu group "Administration" on "Users". In the user admninistration,
you can create an account for each new user.

NOTE - Create a user account for each author. Each page's author will
       then be displayed as part of the page information.

## Troubleshooting

If you have any problems installing or using papaya CMS, please consult
the following resources. This helps us to help you by spending less time
repeatedly answering questions answered elsewhere and concentrate on
developing the system. It also helps us conserve what little hair we have
remaining.

 1. Read the FAQ:           http://www.papaya-cms.com/faq/
 2. Read the docs:          http://www.papaya-cms.com/docs/
 3. Search the forum:       http://www.papaya-cms.com/forum/
 4. Steps 1 - 3 didn't help?<br/>
   -> Write a message in our forum (http://www.papaya-cms.com/forum/)
      Please try to give as much information about your problem as
      possible (ie, operating system, version numbers etc).  This will
      not only help us to track down the problem, but also help those
      users with similar problems who come after you.


##  Appendix

### 1. Install papaya CMS in a subdirectory

It is possible to install papaya CMS in a subdirectory of your
webserver.  You will have to modify the .htaccess file to point to the
subdirectory.  The .htaccess file must remain in your document root.
You can find an example .htaccess in readme directory fo the papaya core
(htaccess.tpl). Substitute the directory name for the placeholder
{%webpath_pages%}.

Examples:

```
  pages/
  cms/page/
```

Note that you may not enter a leading slash (before path), but have to
add a trailing slash (after path).


### 2. Rewrite Rules in httpd.conf

You can put the content of your .htaccess directly in your webservers
configuration file. If possible, use a per-directory configuration.

The .htaccess is then no longer needed and can be disabled, or completely
removed.


### 3. Apache mod_vhost_alias

If you use mod_vhost_alias, the PHP superglobal $_SERVER['DOCUMENT_ROOT']
will give a false value. The installer will fail to calculate the
correct paths. In this case, you have to manually correct the paths in
your conf.inc.php, and add the following line:

```
$_SERVER['DOCUMENT_ROOT'] = '/path/vhosts/hostname/';
```

Please replace '/path/vhosts/hostname/' with the actual path to the virtual
document root of your papaya installation on your webserver.

The paths of the Rewrite Rules in the .htaccess file have to be
corrected as well. If you installed papaya CMS directly into your
document root, you can use the .htaccess from the files directory of
your install package.

### 4. MySQL >= 4.1 and character sets

Starting with Version 4.1, MySQL supports unicode character sets. If
you use MySQL 4.1 or higher, make sure the tables use UTF-8 as default
character set. You can verify this by looking at the table's collation.
It has to start with "utf8" (e.g. utf8_general_ci).

# Start Up

# Routing

Router matches url with section in /application/config/routing.ini

See comments in routing.ini for details.


## Typical Configurations
### PHP Page
- handler: page
- view: includes /content/pages/(view).php
- mvvm not included.
### Peanut Page (KnockoutJS)
- handler: page
- mvvm: name and location of view model and view
- view: optional in rare instance of shared views between view models
### Service
- handler: service
- method: method definded in nutshell/src/cms/routing/ServiceRequestHandler.php

Note that in the case of ServiceCommands, the handler passes the job off to:
ServiceFactory::Execute();

## Site Map
Location: /application/config/sitemap.xml

Defines the menu structure. Pages and services not displayed on the menu are defined in routing.ini only.

The element name may be unique identifier or corresponds to a section key in routing.ini.

If the element contains nested elements, it is rendered as a drop-down sub menu. The identifier in 
the element is appended to the outer element to determine the section key in routing.ini.

The element may declare this attributes:
- Required:
  - title: displayed as menu item
  - description: displayed on hover.
- Optional:
  - uri: overrides the element name and redirects to the URI
  - roles: comma delimited list of authorized roles

# Themes
Theme used is 'default' or as indicated in routing.ini.  This corresponds to a
subdirectory of application\themes

The provided themes are default and plain.  Custom themes may be defined.

## Theme directories

### CSS files
CSS files are located in the root directory of the theme.

A theme must provide styles.css.  It may be a custom css file, see plain, 
or a Bootstrap generated or downloaded styles.css, see default.

A theme my provide extra.css which typically overrides style.css and provides additional styles.

### Include Files
Default and custom themes provide include files in .\inc.  These are included in
application/content/page.php
- page-header.php
- front-header.php
- site-header2.php
- site-footer.php
- menu-column.php

## Page.php 
This file is the master template for pages used by default and custom themes. 

Location: application/content/page.php

Local assets:
- /application/assets/img/favicon.ico
- From theme:
  - styles.css
  - extra.css

External assets:
- Early load
  - Bootstrap icons
  - bootstrap.min.css from CDN (plain only)
  - FontAwesome kit
- Late load
  - Bootstrap scripts from CDN: bootstrap.bundle.min.js
  - If Knockout MVVM:
    - head.load.js (from CDN)
    - PeanutLoader.js (from Peanut core in Nutshells)
Future modifications:
- Override.php in custom themes
- Load non-default bootstrap CDN
- Configure FontAwesome kit URL
- Store head.load.js locally

Questions:
- menutype not supported?
- in Router.php is this needed: $routeData['editorsignedin'] = $user->isAuthorized('editsongs');
If so, generalize or configure.
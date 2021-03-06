#### 1.1.2 ( October 30, 2018 )
* Updated wp tax post binding library.
* Fixed child property issue with inherited attributes.

#### 1.1.1 ( May 29, 2018 )
* Fixed compatibility issue with PHP 5.5.

#### 1.1.0 ( April 19, 2018 )
* Added new default taxonomy Property Location (wpp_location). It automatically takes terms from Address attribute. Can be enabled in composer.json
* Added new default taxonomy Property Type. Can be enabled in composer.json
* Added support of system taxonomies, which can not be set with custom options. Can be enabled in composer.json
* Disabled ability to remove system taxonomy.
* Extended property object with taxonomies terms earlier.
* Extended the draw attributes function with single-value taxonomies being treated as regular attributes.
* Refactored meta box type options on Edit Property page.
* Refactored taxonomy UI on WP-Property Settings page.
* Fixed Warnings and Notices.
* Fixed Fatal Errors.

#### 1.0.2 ( July 28, 2016 )
* Added Add-on's settings to Backup of Current WP-Property Configuration.
* Fixed compatibility with Mandrill plugin.
* "Show in Admin Menu and add native Meta Box" splitted into two separated options. New options are "Show in Admin Menu" and "Add native Meta Box".
* Fixed "Add term post" option.
* Fixed Hierarchical option.
* Added autocomplete option while adding new taxonomies to the property.
* Fixed "access to edit term denied" issue when ""Show in Admin Menu" option enabled.
* Fixed "rewrite slug" option.
* Fixed issue with very long taxonomy names.

#### 1.0.1 ( October 7, 2015 )
* Updated plugin initialisation logic.
* Fixed rewrite rules for hierarchical terms.
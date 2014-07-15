What is Emless-Eff
==================
Emless-Eff (M-F) is an Application Development Framework for people who build websites using PHP. M-F follows an MVC design idea with one major difference, it dropped the M, that is, it does not contain
a model. Removing the model adds a lot of freedom to your development, but you trade predictabilty and regidity for freedom. M-F enables toyu to develop projects must faster than you coul dif you were
writing it from scratch. M-F provides many libraries to help standardize and speed up commonly done tasks, by both the designer and devolper, on your project. M-F helps you stay on task and rapidly
ship and maintain your project without apply strict guidlines to your database design.


Requirements
------------
* PHP version 5.3 or newer.
* A MySQL database


Installation
------------
Please see the Installation section of documentation/userguide.md


Changelog
--------------------------
## Version 1.1.10
Release Date
* Added
* Updated
	* Recoded the SQLQuery class, is now composed of SQLConn, SQLResult, SQLRow and SQLTable
	* SQL classes can properly be embeded into others for easy data manipulation
* Fixed
* Removed


## Version 1.1.9
Release Date July 15th, 2013
* Added
	* Added PDO transactions to SQL library
	* Added apply_functo_to_cells method to SQL Result, used to apply a function to all cells in results
	* Added ability to embed SQL Results into other results
	* Added run_as_script method to framework class (experimental)
* Updated
	* Updated SQLResult, error methods now check DBH for error
	* Update base controller, beforeAction and afterAction now declared by default
	* Updated SQL Result and SQL Row as_array methods to properly return embeded SQL Results
	* Updated bootstrap, now self contained and no longer dependant on index.php
	* Updated framework class, re-structure code to improve performance and maintainability and clear up memory
* Fixed
	* Fixed SQLRow, now properly accepts setting data outside of initialization
	* Fixed template bug that would improperly load admin views
	* Fixed admin class instantiation


## Version 1.1.8
Release Date: January 14th, 2014

* Added
	* Added dbh to registry to increase compatibility when updating
	* Added Ajax Hooks
* Updated
	* DBH calls are now always objects. Use as_array() method instead
	* Init Hooks are no longer called on ajax calls
* Fixed
	* Fix framework action, no longer static. No longer recreates an instance of the framework on module calls
	* Tracker no longer tracks module calls


## Version 1.1.8
Release Date: January 14th, 2014

* Added
	* Added dbh to registry to increase compatibility when updating
	* Added Ajax Hooks
* Updated
	* DBH calls are now always objects. Use as_array() method instead
	* Init Hooks are no longer called on ajax calls
* Fixed
	* Fix framework action, no longer static. No longer recreates an instance of the framework on module calls
	* Tracker no longer tracks module calls



## Version 1.1.7
Release Date: November 20th, 2013

* Added
	* Added uploader library
	* Added controller and action to registry
	* Added slice method to SQLResult to get a subset of results
	* Added shuffle method to SQLResult to randomize results
	* Added is_dev method
* Update
	* All framework registered registry entries now start with _
	* SQL clean now accepts 'noquote' as parameter, wont wrape in quotes
	* Updated status_array method, now accepts and returns extra data
	* Renamed SQLResult all method to search
* Fixed
	* Fixed default include path for views, would break on some servers
	* Fix form function, now accepts strings and array properly
	* Admin html now properly accepts the same options as public
	* Fix ignore files in temp folder
	* HTML5 doctype not proper
	* FIxed html attributes


## Version 1.1.6
Release Date: August 1st, 2013

* Added
	* Added all method to SQLResult to get all fields from a table, as array
* Updated
	* Updated Library loading, can now pass paramters required for constructor
	* Updated Library loading, can now only include library file without instantiating
	* Updated SQLRow capabilities to edit and add data post query
	* Updated controller and template logic to better handle admin functions, admin logic now controller heavy
	* Updated debugging, now shows post and get arrays
* Fixed
	* Fixed template render logic
* Removed
	* Removed SQLRow add_value method, redundant


## Version 1.1.5
Release Date: July 16th, 2013

* Added
	* Added SQLRow class to SQLQuery file
	* Added ability to add custom data to SQLResults and SQLRow objects
* Updated
	* Updated include_file method in Template, now accepts parameters
	* Updated SQLResult, now stores data in SQLRow class
* Fixed
	* Fixed HTML options, now properly works with defaults


## Version 1.1.4
Release Date: June 12th, 2013

* Added
	* Added remove_view method to controller, can unset views
* Updated
	* Updated HTML library, options can now be passed as strings
	* Updated SQLQuery, can now set wether the default return type for query is an object or not
* Fixed
	* Fixed err_check default class name


## Version 1.1.3
Release Date: May 3rd, 2013

* Added
	* Added public files folder
	* Added canonical function to html library
* Updated
	* Updated SQLResult, made serializable
	* Updated Profiler, added identifier for sites running the same DB
	* Updated Profiler, added sample rate to core.php
* Fixed
	* Fixed tracker, can now be disabled properly
	* Fixed tracker, properly returns data even when not enabled
	* Fixed htaccess to prevent spoofing
	* Fixed Loader, no longer crashes due to case sensitive OS'
	* Fixed Loader, properly unloads
	* Fixed Profiler, no longer throws Undefined index errors
* Removed
	* Removed core functions helper to reduce redundancy


## Version 1.1.2
Release Date: April 4th, 2013

* Updated
	* Updated errCheck, now err_check, allows the passing of options to be added like html attributes
	* Updated error loggin, now defaults to framework log file if possible
	* Updated HTML library, improved cache retention
	* Updated libraries, they now have access to load() method and improved inheritance
	* Updated SQLResult to auto return first()
* Fixed
	* Fixed bug in SQLResult class that skipped first element after first run through
* Removed
	* Removed log_error function to reduce redundancies


## Version 1.1.1
Release Date: March 12th, 2013

* Updated
	* Updated Libraries, all libraries now have to extend Library
* Fixed
	* Fixed framework globals, now returns error if index is not set
	* Fixed form method in HTML library, would throw error when no action passed
	* Fixed Template to load included_files for admin properly
	* Fixed SQLQUery bug that would not return query object, as object
	* Fixed Loader to properly load helpers and libraries for admin
	* Fixed forward and aforward methods to work with domains with port numbers
	* Fixed Template to properly load AHTML library


## Version 1.1
Release Date: March 9th, 2013

* Added
	* Added admin specific helpers, libraries and scripts
	* Added admin specific HTML library to core
* Fixed
	* Fixed defaultViews bug that would add views in reverse
	* Fixed url routing bug that affected hyphens and underscores


## Version 1.0.6
Release Date: March 8th, 2013

* Added
	* Added core and schema config files
	* Added default views, can now load more than 1 view by default as header and footer
* Updated
	* Updated config file setup
* Fixed
	* Fixed URL routing error


## Version 1.0.5
Release Date: March 6th, 2013

* Added
	* Added clear_views method to controller
* Fixed
	* Fixed HTML library bug that would improperly escape quotes
	* Fixed problem with loading views


## Version 1.0.4
Release Date: February 27th, 2013
* Updated
	* Cache library is no longer static
	* AJAX library is no longer static
	* Link and Form HTML methods now have secure alternatives
	* Loader library method now returns a reference to the library
* Fixed
	* Fixed incorrect path for cache files


## Version 1.0.3
Release Date: February 25th, 2013

* Added
	* Added Tracker class, tracks user around the framework
* Updated
	* Updated framework, added support for Tracker
	* Updated controller, added support for Tracker
	* Updated SQLQuery query_obj, rows without meta data now go to "fn" key
	* Updated HTML library, added link_open and link_close methods to allow flexibilty for design
* Fixed
	* Fixed session issue making it inaccessible in config files
* Removed
	* Removed Tracker library


## Version 1.0.2
Release Date: February 18th, 2013

* Updated
	* Template module method now accepts query string paramters
	* HTML meta method now accepts options parameter
	* HTML icon method now accepts options parameter


## Version 1.0.1
Release Date: February 15th, 2013

* Added
	* Added SQLResult class
	* Added query_obj method to SQLQuery, now supports returning an SQLResult object
	* Added Profiler Ignore List file
* Updated
	* SQLQuery class, removed MySQL PHP support in favor of PDO
	* Profiler, added support for ignored pages


## Version 1.0
Release Date: January 18th, 2013

* Added
	* Added profiler class
	* Added support for application level helpers and library files
	* Added file linking support
* Updated
	* Moved files folder location


## Version Beta 0.9
Release Date: January 11th, 2013

* Added
	* Added framework documentation
* Updated
	* Seperated application and system logic for easier updating and management


License
-------
Please see documentation/license.txt

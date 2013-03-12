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

## Version 1.1.1
Release Date: 

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


Acknowledgement
---------------
Anant Garg for providing me with the idea and the base for this framework, Skye and Jon for providing me with the opportunity to code and actively develope this framework	
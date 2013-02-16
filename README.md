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


Changelog and New Features
--------------------------

## Planned Changes
* Major Changes
	* Improve Administration tools
	* Create proper documentation


## Version 1.0.1
Release Date: February 15th, 2013

* New
	* Added SQLResult class
	* Added Profiler Ignore List file
* Updated
	* SQLQuery class, removed MySQL PHP support in favor of PDO
	* SQLQuery query method, now supports returning an SQLResult object
	* Profiler, added support for ignored pages

## Version 1.0
Release Date: January 18th, 2013

* New
	* Added profiler class
	* Added support for application level helpers and library files
	* Added file linking support
* Updated
	* Moved files folder location

### Version Beta 0.9
Release Date: January 11th, 2013

* New
	* Added framework documentation
* Updated
	* Seperated application and system logic for easier updating and management


License
-------
Please see documentation/license.txt


Acknowledgement
---------------
Anant Garg for providing me with the idea and the base for this framework, Skye and Jon for providing me with the opportunity to code and actively develope this framework	
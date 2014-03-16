Cloud Phalcon Skeleton
================

What's this?
---------------
The Cloud 9 Living team (www.cloud9living.com) is rewriting our system on Phalcon. 
The dev tools skeleton was not adequate for the application we were building, so I thought I'd share our solution
The skeleton is from a mix of architectures, drawing most heavily from magento + zend.
The idea is to provide a Phalcon skeleton that is more robust than the one created by phalcon dev tools

Why should I use it?
------------------
* Autoload all modules in the /core/ directory
* Separate views/layouts from the rest of your code
* Autoload external libraries into one namespace
* Built in support for memcached sessions (using the memcached class from the phalcon incubator)
* Automatically creates caching services for you and provides easy access to them
* Global access to entire application via the Cloud static class
* Global registry via the Cloud static class
* Abstracted module class so you can create modules more quickly
* Exception and error handling, with logging
* Provides configuration options for development, staging, and production
* Trait classes for models, singletons, and flexible data objects (from the magento Varien_Object)

Requirements
------------

* PHP >= 5.3.9
* Phalcon >= 1.2.6

How do I use it?
=======================
* Clone this repository and navigate to app/
* You'll find several folders:
* app/code --> all your modules live here, view the Core module for an example of how to register a new one
* app/design --> all your views and layouts live here
* lib/     --> all your external libraries live here
* app/config --> the main class reads all .php files from here and parses them as config directives. 
* app/code/Core/Library/ObjectTraits --- singleton,factory, and data-object traits


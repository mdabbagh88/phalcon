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
* Automatically loads modules --- just create the directory in app/code and you're good!
* Separate views/layouts from the rest of your code
* Autoload external libraries into one namespace
* Built in support for memcached sessions (using the memcached class from the phalcon incubator)
* Caching service created on bootup, easy access with debugging
* Global access to entire application
* Option to "run" application or just boot it up (good for CLI stuff)
* Global registry via the Cloud static class
* Abstracted module class so you can create modules more quickly
* Exception and error handling, with logging
* Provides configuration options for development, staging, and production
* Trait classes for models, singletons, and flexible data objects (from the magento Varien_Object)

Requirements
------------

* PHP >= 5.3.9
* Phalcon >= 1.2.6

How do I use these features?
=======================
If you look at the skeleton you should be able to figure out most of it by example.

A few items:

Application layout
* app/code --- your modules live here
* app/design --- layouts/views
* app/lib --- external libraries
* app/var --- cache, sessions (files) and automatically created here
* app/public --- entry point

Global Entry
* require_once("/path/to/app/Cloud.php")
* Cloud::app() --- returns booted up application
* Cloud::run() --- runs the app

Global Registry
* Cloud::register($key, $value)
* Cloud::registry($key) --- retrieval

Exception / Error handling
* Based on the application status, exceptions will be printed to screen or only logged
* Logs are in app/var/logs

Applicaiton Status & Configuration
* The app automatically loads all .php files in app/config
* Look at the example config files, based on which server the app detects you can load different values
* Customize \Cloud\Core\Model\App::isServer to add your own logic for dev,staging,live modes!

Autoload module
* Add your module to app/code (see the Core module for example)
* Created your Module.php and extend it from \Cloud\Core\Model\AbstractModule
* Clear the cache or disable it and you're good to go!

Cache Use
* Cloud::app()->loadCache($key, $callback) --- from anywhere in the app!
* Cloud::app()->saveCache($key, $value) --- anywhere in app!

Cache Clearing / Debugging
* Append ?clearCache to clear it
* Append ?debugCache to debug it (see hits, misses)

Models, Singleton
* Any class name ::instance() will give you either a new instance or singleton instance
* Extend \Cloud\Core\Model\AbstractModel or, implement trait \Cloud\Core\Library\ObjectTrait\Factory for models
* Extend \Cloud\Core\Model\AbstractSingleton or implement trait \Cloud\Core\Library\ObjectTrait\Singleton for singletons

Magic Data Trait
* This is a straight port from Magento's Varien_Object
* It's now a trait located at \Cloud\Core\Library\ObjectTrait\DataObject

Contribute / Feedback
=======================
Please feel free to provide any feedback/corrections or contribute!

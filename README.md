Phalcon PHP Skeleton
======================


Phalcon's MVC setup. It provides support for the following:

* Better module loading system (through etc/modules/Module_Name.xml) --- no need to use the phaclon Module.php
* Multiple websites linked to the database
* Separate design directories for each website. The directories can also be setup to "fall back" from an override package to the default package (great for things like holiday skins, etc)
* Cloud-dev-tools for quickly creating models in the appropriate module --- adds a "generated" code block to your models so they aren't overwritten when you re-run the tool.
* Separate views/layouts from the rest of your code
* Autoload external libraries into one namespace
* Built in support for memcached sessions (using the memcached class from the phalcon incubator)
* Built in support for Redis sessions / cache (using phalcon incubator)
* Overriden front controller and init process (gutted Phalcon defaults)
* The ability to register event observers and module front names in the module configuration files.
* Built in support for a url reWriter / redirect (Just insert rows into core_url_rewrite)
* Caching service created on bootup, easy access with debugging
* Global access to entire application
* Option to "run" application or just boot it up (good for CLI stuff)
* Global registry via the Cloud static class
* Exception and error handling, with logging
* Provides configuration options for development, staging, and production
* Trait classes for models, singletons, and flexible data objects (from the magento Varien_Object)

Explation of Directories
=========================
* app/etc/config --- Configurations for development, staging, production, global, and local --- local.php is the only file that should not be in your repo
* app/etc/modules --- all *.xml files get loaded as module configurations here
* app/code --- all modules are located here (controllers, librarires, helpers, widgets, models)
* app/design --- all website packages are located here
* db --- sql scripts here
* developer/cloud-dev-tools --- contains a script to clear cache as well as create a model in the appropriate module (and read its fields from the db)
* lib --- trated like an extra module, drop in external libraries here
* node_modules --- for grunt
* public --- entry point to web app, js, css, etc
* var/cache --- file cache
* var/volt --- compiled volt templates
* var/session --- file sessions
* var/log --- error.log and exception.log
*

Getting Started
============================
* You will want to run the install script in db/install-3.0.1.sql
* Update your app/etc/config/local.php to set the application status to development (just rename local.php.sample to local.php)
* Update your app/etc/config/development.php settings
* Set apache/nginx to public/ as the web root
* Set directory index index.php
* You should be good to go!


More to come when I have time to comment!

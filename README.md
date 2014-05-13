cloud-phalcon-skeleton
======================

Cloud 9 Living Phalcon PHP Skeleton

Version 3.0.1 out now! This is a very robust update to Phalcon's MVC setup. It provides support for the following:

* Better module loading system (through etc/modules/Module_Name.xml) --- no need to use the phaclon Module.php
* Multiple websites linked to the database
* Separate design directories for each website. The directories can also be setup to "fall back" from an override package to the default package (great for things like holiday skins, etc)
* Cloud-dev-tools for quickly creating models in the appropriate module
* Separate views/layouts from the rest of your code
* Autoload external libraries into one namespace
* Built in support for memcached sessions (using the memcached class from the phalcon incubator)
* Built in support for Redis sessions / cache (using phalcon incubator)
* Overriden front controller and init process (gutted Phalcon defaults)
* The ability to register event observers and module front names in the module configuration files.
* Built in support for a url rewriter / redirect (Just insert rows into core_url_rewrite) 
* Caching service created on bootup, easy access with debugging
* Global access to entire application
* Option to "run" application or just boot it up (good for CLI stuff)
* Global registry via the Cloud static class
* Abstracted module class so you can create modules more quickly
* Exception and error handling, with logging
* Provides configuration options for development, staging, and production
* Trait classes for models, singletons, and flexible data objects (from the magento Varien_Object)

More to come when I have time to comment!

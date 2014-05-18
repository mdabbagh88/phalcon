<?php

return array(
    "application" => array(
        "status"   => \Cloud\Core\Model\App::APP_STATUS_DEVELOPMENT,
        "base_uri" => "/",
        "cache"    => array(
            "enabled" => true,
            "layers"  => array(
                array(
                    "backend"  => \Cloud\Core\Model\App\Cache::CACHE_BACKEND_REDIS,
                    "port"     => "6379",
                    "host"     => "localhost",
                    "priority" => 1
                ),
                array(
                    "backend"  => \Cloud\Core\Model\App\Cache::CACHE_BACKEND_FILE,
                    "cacheDir" => CLOUD_ROOT . DS . "var" . DS . "cache" . DS,
                    "priority" => 2
                )
            )
        ),
        "session"  => array(
            "save_path" => \Cloud\Core\Model\App\Session::SESSION_SAVE_REDIS,
            "host"      => "localhost",
            "port"      => "6380"
        )
    ),
    "database"    => array(
        "host"     => "localhost",
        "username" => "root",
        "password" => "meabed",
        "dbname"   => "ads",
        "adapter"  => "pdo_mysql" //In format pdo_[type] where [type] = mysql|oracle|postgresql|sqlite
    )
);
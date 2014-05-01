<?php

/** @var $this \Cloud\Core\Model\App */
$_config = $this->getConfig();

/** Settings for local installation **/
if ($this->isServer("development")){
    $_local_config = new \Phalcon\Config(array(
        "database" => array(
            "host"     => "localhost",
            "username" => "root",
            "password" => "meabed",
            "dbname"   => "ads",
            "adapter"  => "pdo_mysql" //In format pdo_[type] where [type] = mysql|oracle|postgresql|sqlite
        )
    ));
    $_config->merge($_local_config);
/** END Local Installation Settings **/

/** Settings for staging installation **/
}elseif ($this->isServer("staging")){




    /** Settings for production installation **/
}elseif ($this->isServer("production")){




}

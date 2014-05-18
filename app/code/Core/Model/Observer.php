<?php
namespace Cloud\Core\Model;

Class Observer extends \Cloud\Core\Model\App\Events\Observer
{
    public function testPickup($event, $source, $extraData = false)
    {
        //  echo "IN TEST 1"; exit;
    }

    public function testPickup2($event, $source, $extraData = false)
    {
        //   echo "IN TEST 2"; exit;
    }
}
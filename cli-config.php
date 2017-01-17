<?php

// This file is for doctrine cli script
// run: ./vendor/bin/doctrine

use Linkman\Linkman;

require_once 'vendor/autoload.php';

// Any way to access the EntityManager from  your application
$em = (new Linkman("./"))->db();

return new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

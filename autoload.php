<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('Cissee\\Webtrees\\Module\\ExtendedRelationships\\', __DIR__);
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");
$loader->addPsr4('Cissee\\WebtreesExt\\Services\\', __DIR__ . "/patchedWebtrees/Services");
$loader->addPsr4('Cissee\\WebtreesExt\\Functions\\', __DIR__ . "/patchedWebtrees/Functions");
$loader->register();

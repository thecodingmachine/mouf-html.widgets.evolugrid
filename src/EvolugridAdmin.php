<?php
use Mouf\MoufManager;
use Mouf\MoufUtils;


MoufUtils::registerMainMenu('htmlMainMenu', 'HTML', null, 'mainMenu', 40);
MoufUtils::registerMenuItem('htmlEvolugridMainMenu', 'Evolugrid', null, 'htmlMainMenu', 20);
MoufUtils::registerMenuItem('htmlEvolugridCreateInstance', 'Create a new Evolugrid', 'evolugrid/newEvolugrid', 'htmlEvolugridMainMenu', 10);


// Controller declaration
$moufManager = MoufManager::getMoufManager();
$moufManager->declareComponent('evolugrid', 'Mouf\\Html\\Widgets\\Evolugrid\\Controllers\\EvolugridAdminController', true);
$moufManager->bindComponents('evolugrid', 'template', 'moufTemplate');
$moufManager->bindComponents('evolugrid', 'content', 'block.content');

?>
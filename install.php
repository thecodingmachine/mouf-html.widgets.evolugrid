<?php
// First, let's request the install utilities
require_once '../../../../../mouf/actions/InstallUtils.php';

// Let's init Mouf
InstallUtils::init(InstallUtils::$INIT_APP);

// Let's create the instance
$moufManager = MoufManager::getMoufManager();

if ($moufManager->instanceExists("evolugridLibrary")) {
	$evolugridLib = $moufManager->getInstanceDescriptor("evolugridLibrary");
} else {
	$evolugridLib = $moufManager->createInstance("WebLibrary");
	$evolugridLib->setName("evolugridLibrary");
}
$evolugridLib->getProperty("jsFiles")->setValue(array(
	'plugins/html/widgets/evolugrid/1.0/js/evolugrid.js'
));
$renderer = $moufManager->getInstanceDescriptor('defaultWebLibraryRenderer');
$evolugridLib->getProperty("renderer")->setValue($renderer);
$evolugridLib->getProperty("dependencies")->setValue(array($moufManager->getInstanceDescriptor('jQueryLibrary')));

$webLibraryManager = $moufManager->getInstanceDescriptor('defaultWebLibraryManager');
if ($webLibraryManager) {
	$libraries = $webLibraryManager->getProperty("webLibraries")->getValue();
	$libraries[] = $evolugridLib;
	$webLibraryManager->getProperty("webLibraries")->setValue($libraries);
}

// Let's rewrite the MoufComponents.php file to save the component
$moufManager->rewriteMouf();

// Finally, let's continue the install
InstallUtils::continueInstall();
<?php
switch (PHP_OS) {
	default:
		require_once '/usr/local/lib/php/symfony/autoload/sfCoreAutoload.class.php';
		break;
	case 'Darwin':
		require_once '/usr/local/lib/php/PEAR/symfony/autoload/sfCoreAutoload.class.php';
		break;
}
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration {
	public function setup() {
		$this->enablePlugins('sfDoctrinePlugin');
	}
}

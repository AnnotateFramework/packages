<?php

namespace Annotate\Packages\DI;


use Annotate\Packages\Latte\Macros;
use Annotate\Packages\Loaders\AssetsLoader;
use Annotate\Packages\Loaders\PackageLoader;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\CompilerExtension;


class PackagesExtension extends CompilerExtension
{

	private $defaults = [
		'directories' => [
			'%appDir%/../www/bower_components/',
		],
		'rootDir' => '%appDir%/../www'
	];



	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('packageLoader'))
			->setClass(PackageLoader::class, [
				'directories' => $config['directories'],
				'rootDir' => $config['rootDir'],
			])
			->addTag(EventsExtension::TAG_SUBSCRIBER);

		$builder->addDefinition($this->prefix('assetsLoader'))
			->setClass(AssetsLoader::class)
			->addTag(EventsExtension::TAG_SUBSCRIBER);

		$latteFactory = $this->getLatteFactory();
		$latteFactory->addSetup('?->onCompile[] = function($engine) { ' . Macros::class . '::install($engine->getCompiler()); }', ['@self']);
	}



	/**
	 * @return ServiceDefinition
	 */
	private function getLatteFactory()
	{
		$builder = $this->getContainerBuilder();
		return $builder->hasDefinition('nette.latteFactory')
			? $builder->getDefinition('nette.latteFactory')
			: $builder->getDefinition('nette.latte');
	}

}

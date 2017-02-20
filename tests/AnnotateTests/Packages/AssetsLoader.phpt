<?php

namespace AnnotateTests\Packages;

use Annotate\Packages\Loaders\AssetsLoader;
use Annotate\Packages\Package;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\Template;
use Tester;
use Tester\Assert;


require_once __DIR__ . '/../bootstrap.php';


class AssetsLoaderTest extends TestCase
{

	/** @var  AssetsLoader */
	private $assetsLoader;



	public function setUp()
	{
		$this->assetsLoader = new AssetsLoader();
	}



	public function testItImplementsSubscriber()
	{
		Assert::true($this->assetsLoader instanceof \Kdyby\Events\Subscriber);
	}



	public function testAddPackageAppendsPackages()
	{
		$package = new Package('TestPackage', 0.1, NULL, NULL, NULL, NULL);
		Assert::equal(0, count($this->assetsLoader->getPackages()));
		$this->assetsLoader->addPackage($package);
		Assert::equal(1, count($this->assetsLoader->getPackages()));
		Assert::true(in_array($package, $this->assetsLoader->getPackages()));
	}



	public function testAddStylesMergeAddedWithExistingArray()
	{
		Assert::equal([], $this->assetsLoader->getStyles());
		$styles = [
			'style.css',
		];
		$this->assetsLoader->addStyles($styles);
		Assert::equal($styles, $this->assetsLoader->getStyles());
		$anotherStyles = [
			'another.css'
		];
		$this->assetsLoader->addStyles($anotherStyles);
		Assert::equal(array_merge($styles, $anotherStyles), $this->assetsLoader->getStyles());
	}



	public function testAddScriptsMergeAddedWithExistingArray()
	{
		Assert::equal([], $this->assetsLoader->getScripts());
		$scripts = [
			'script.js',
		];
		$this->assetsLoader->addScripts($scripts);
		Assert::equal($scripts, $this->assetsLoader->getScripts());
		$anotherScripts = [
			'another.js'
		];
		$this->assetsLoader->addScripts($anotherScripts);
		Assert::equal(array_merge($scripts, $anotherScripts), $this->assetsLoader->getScripts());
	}



	public function testItListensGoodEvents()
	{
		Assert::equal(
			[
				'Annotate\\Templating\\TemplateFactory::onSetupTemplate',
			],
			$this->assetsLoader->getSubscribedEvents()
		);
	}



	public function testItAddsTemplateVariablesOnSetupTemplate()
	{
		$template = new Template(new Engine);
		$this->assetsLoader->onSetupTemplate($template);
		Assert::equal([], $template->assetsLoader->styles);
		Assert::equal([], $template->assetsLoader->scripts);
	}

}


\run(new AssetsLoaderTest);

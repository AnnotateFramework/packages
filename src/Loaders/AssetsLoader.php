<?php

namespace Annotate\Packages\Loaders;

use Annotate\Packages\Package;
use Annotate\Packages\PlainAsset;
use Kdyby\Events\Subscriber;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Object;


class AssetsLoader extends Object implements Subscriber
{

	private $styles = [];

	private $scripts = [];

	/** @var Package[] */
	private $packages = [];



	public function getPackages()
	{
		return $this->packages;
	}



	public function addPackage(Package $package)
	{
		$this->packages[] = $package;
	}



	public function getStyles()
	{
		return $this->styles;
	}



	public function getScripts()
	{
		return $this->scripts;
	}



	public function getSubscribedEvents()
	{
		return [
			'Annotate\\Templating\\TemplateFactory::onSetupTemplate',
		];
	}



	public function addStyles($styles)
	{
		$this->styles = array_merge($this->styles, $styles);
	}



	public function addScripts($scripts)
	{
		$this->scripts = array_merge($this->scripts, $scripts);
	}



	public function addAsset($asset)
	{
		$ext = pathinfo($asset, PATHINFO_EXTENSION);
		$asset = new PlainAsset($asset);

		switch ($ext) {
			case 'css':
				$this->styles[] = $asset;
				break;
			case 'js':
				$this->scripts[] = $asset;
				break;
		}
	}



	public function onSetupTemplate(Template $template)
	{
		$template->assetsLoader = $this;
	}

}

<?php

namespace Annotate\Packages;

use Annotate\Framework\Utils\Strings;


final class Asset implements IAsset
{

	/** @var Package */
	private $package;

	private $fileName;



	public function __construct(Package $package, $fileName)
	{
		$this->package = $package;
		$this->fileName = $fileName;
	}



	public function getVersion()
	{
		if (file_exists($this->getAbsolutePath())) {
			return trim(filemtime($this->getAbsolutePath()));
		}
		return 0;
	}



	public function getAbsolutePath()
	{
		if (Strings::startsWith($this->fileName, '//')) {
			return $this->fileName;
		}
		return $_SERVER['DOCUMENT_ROOT'] . $this->getRelativePath(NULL);
	}



	public function getRelativePath($basePath)
	{
		if (Strings::startsWith($this->fileName, '//')) {
			return $this->fileName;
		}
		return str_replace('@', $basePath . $this->package->getRelativePath(), $this->fileName);
	}

}

<?php

namespace Annotate\Packages;


final class PlainAsset implements IAsset
{

	/** @var string */
	private $fileName;



	public function __construct($fileName)
	{
		$this->fileName = $fileName;
	}



	public function getRelativePath($basePath)
	{
		return $this->fileName;
	}



	public function getAbsolutePath()
	{
		return $_SERVER['DOCUMENT_ROOT'] . $this->fileName;
	}



	public function getVersion()
	{
		return 0;
	}

}

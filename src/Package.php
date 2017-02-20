<?php

namespace Annotate\Packages;


class Package
{

	private $loaded = FALSE;

	private $checked = FALSE;

	private $name;

	private $aDir;

	private $rDir;

	private $version;

	private $variants;

	private $dependencies = [];



	public function __construct($name, $version, $variants, $dependencies, $aDir, $rDir)
	{
		$this->name = strtolower($name);
		$this->version = $version;
		$this->variants = $variants;
		$this->dependencies = $dependencies;
		$this->aDir = $aDir;
		$this->rDir = $rDir;
	}



	public function isLoaded()
	{
		return $this->loaded;
	}



	public function setLoaded()
	{
		$this->loaded = TRUE;
	}



	public function isChecked()
	{
		return $this->checked;
	}



	public function setChecked()
	{
		$this->checked = TRUE;
	}



	public function getName()
	{
		return $this->name;
	}



	public function getVersion()
	{
		return $this->version;
	}



	public function getVariants()
	{
		return $this->variants;
	}



	public function getDependencies()
	{
		return $this->dependencies;
	}



	public function getRelativePath()
	{
		return str_replace('\\', '/', $this->rDir) . '/';
	}



	public function hasVariant($name)
	{
		if (isset($this->variants[$name]) && $this->variants[$name] != NULL) {
			return TRUE;
		}

		return FALSE;
	}



	public function __toString()
	{
		return "{$this->name} {$this->version}";
	}

}

<?php

namespace Annotate\Packages\Parsers;

use Annotate\Packages\Package;
use Nette\DI\Config\Adapters\NeonAdapter;


final class NeonParser implements IParser
{

	/** @var NeonAdapter */
	private $neonAdapter;

	/** @var string */
	private $rootDir;



	public function __construct(NeonAdapter $neonAdapter, $rootDir)
	{
		$this->neonAdapter = $neonAdapter;
		$this->rootDir = $rootDir;
	}



	public function parse($filePath)
	{
		$neon = $this->neonAdapter->load($filePath);
		$this->mergeVariants($neon);
		$aDir = dirname($filePath);
		return new Package(
						$neon['name'],
						$neon['version'],
						$neon['variants'],
						isset($neon['dependencies']) ? $neon['dependencies'] : NULL,
						$aDir,
						str_replace($this->rootDir, NULL, $aDir)
		);
	}



	private function mergeVariants(&$neon)
	{
		foreach ($neon['variants'] as $name => $variant) {
			if (isset($variant['_extends'])) {
				$extendsName = $variant['_extends'];
				if (!isset($neon['variants'][$extendsName])) {
					throw new \RuntimeException('Cannot extend package variant "' . $name . '". Undefined package variant "' . $extendsName . '"');
				}
				$extends = $neon['variants'][$extendsName];
				if (!isset($variant['styles'])) {
					$variant['styles'] = [];
				}
				if (!isset($variant['scripts'])) {
					$variant['scripts'] = [];
				}
				if (!isset($extends['styles'])) {
					$extends['styles'] = [];
				}
				if (!isset($extends['scripts'])) {
					$extends['scripts'] = [];
				}

				foreach ($extends['styles'] as $extendsStyle) {
					array_unshift($variant['styles'], $extendsStyle);
				}

				foreach ($extends['scripts'] as $extendsScript) {
					array_unshift($variant['scripts'], $extendsScript);
				}

				$neon['variants'][$name]['styles'] = $variant['styles'];
				$neon['variants'][$name]['scripts'] = $variant['scripts'];
				unset($variant['_extends']);
			}
		}
	}


}

<?php

namespace Annotate\Packages\Parsers;

use Annotate\Packages\Package;
use Nette\Utils\Json;


final class BowerParser implements IParser
{

	/** @var string */
	private $rootDir;



	public function __construct($rootDir)
	{
		$this->rootDir = $rootDir;
	}



	public function parse($filePath)
	{
		$data = Json::decode(file_get_contents($filePath), Json::FORCE_ARRAY);

		$aDir = dirname($filePath);
		$rDir = str_replace($this->rootDir, NULL, $aDir);
		$dependencies = isset($data['dependencies']) ? $data['dependencies'] : NULL;

		if (isset($data['main'])) {
			$files = is_array($data['main']) ? $data['main'] : [$data['main']];
		} elseif (isset($data['scripts'])) {
			$files = is_array($data['scripts']) ? $data['scripts'] : [$data['scripts']];
		} else {
			$files = [];
		}

		$scripts = [];
		$styles = [];

		foreach ($files as $filename) {
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if ($ext === 'css') {
				$styles[] = '@' . ltrim($filename, '.');
			}
			if ($ext === 'js') {
				$scripts[] = '@' . ltrim($filename, '.');
			}
		};

		return new Package(
						$data['name'],
						isset($data['version']) ? $data['version'] : NULL,
						[
							'default' =>
								[
									'styles' => $styles,
									'scripts' => $scripts,
								],
						],
						$dependencies,
						$aDir,
						$rDir
		);

	}

}

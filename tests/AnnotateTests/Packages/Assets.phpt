<?php

namespace AnnotateTests\Packages;

use Annotate\Packages\Asset;
use Annotate\Packages\Package;
use Tester;
use Tester\Assert;


require_once __DIR__ . '/../bootstrap.php';


class AssetsTest extends TestCase
{

	public function testAssetReturnsCorrectPath()
	{
		$package = new Package(
			'Package',
			'2.0',
			[
				'default' => []
			],
			[],
			'/adir/to/package',
			'/package'
		);
		$asset = new Asset($package, '@css/file.css');
		Assert::same('/basepath/package/css/file.css', $asset->getRelativePath('/basepath'));
	}

}


\run(new AssetsTest);

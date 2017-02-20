<?php


namespace Annotate\Packages\Latte;


use Annotate\Packages\Loaders\AssetsLoader;
use Latte;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;


class Macros extends MacroSet
{

	public static function install(Latte\Compiler $parser)
	{
		$me = new static($parser);
		$me->addMacro('asset', function (MacroNode $node, PhpWriter $writer) use ($me) {
			return $me->macroAsset($node, $writer);
		}, NULL, function (MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> src="<?php ' . $me->macroAsset($node, $writer) . ' ?>"<?php ';
		});
	}



	public function macroAsset(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('$presenter->context->getByType(\'' . AssetsLoader::class . '\')->addAsset($basePath . %node.word)');
	}

}

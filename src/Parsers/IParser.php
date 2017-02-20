<?php

namespace Annotate\Packages\Parsers;

use Annotate\Packages\Package;


interface IParser
{

	/**
	 * @param  string
	 * @return Package
	 */
	function parse($filePath);

}

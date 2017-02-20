<?php

namespace Annotate\Packages;

interface IAsset
{

	function getAbsolutePath();



	function getRelativePath($basePath);



	function getVersion();

}

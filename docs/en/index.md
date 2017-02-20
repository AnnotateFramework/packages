Quickstart
==========

This extension provides support for loading css/js packages dynamically. You define a package and require this package in your presenter or component.
Packages extension also manage dependencies of packages so you can load eg. jQueryUI package in your component and jQuery will be automatically loaded before jQueryUI.

It also support loading of bower packages.

Installation
------------

Require this extension by [Composer](http://getcomposer.org)

```sh
$ composer require annotate/packages:@dev
```

Register extension into configuration:

```yml
extensions:
    packages: Annotate\Packages\DI\PackagesExtension
```

Configuration
-------------

Default directory to find packages is set to `%appDir%/addons/packages`. So this directory must exist. To change directory for packages just update configuration:

```yml
packages:
    directory: %appDir%/packages
```

Template setup
--------------

This @layout.latte structure is recommended to be able to load scripts in components dynamically:

```smarty
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {capture $html}
</head>
<body>
    {include #content}
    {* place any <body> content here *}
    {/capture}

    {foreach $assetsLoader->getStyles() as $style}
        <link rel="stylesheet" href="{$style->getRelativePath($basePath)}"/>
    {/foreach}
    {$html|noescape}

    {foreach $assetsLoader->getScripts() as $script}
        <script src="{$script->getRelativePath($basePath)}"></script>
    {/foreach}
</body>
</html>

```

The `capture` macro will take all content. Thanks to this all scripts and css will be loaded dynamically even if you require them from component.

Pass assetsLoader to the template
---------------------------------

Edit your presenter:

```php

use Annotate\Packages\Loaders\AssetsLoader;
use Annotate\Packages\Loaders\PackageLoader;

class Presenter extends Nette\Application\UI\Presenter {

    /** @var AssetsLoader @inject */
    public $assetsLoader;

    /** @var PackageLoader @inject */
    public $assetsLoader;

    protected function startup()
    {
        parent::startup();
        $this->packageLoader->loadPackage('jQueryUI');
    }

    protected function beforeRender()
    {
        $this->template->assetsLoader = $this->assetsLoader;
    }



}
```

When you check your page in browser jQueryUI assets will be loaded after jQuery assets.

Require asset from template
---------------------------
From version 1.0.1 you can add assets to AssetLoader directly in template. You are provided by `asset` macro:

```latte
{asset /css/style.css}
```

Note: assets are loaded relatively to your `$basePath`.

Next steps
----------

- [define package](define_package.md)
- [require package](require_package.md)

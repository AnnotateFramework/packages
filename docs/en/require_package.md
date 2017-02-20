Require package
===============

Just inject `Annotate\Packages\Loaders\PackageLoader` to presenter/component/service and call `loadPackage` method.
You can specify minimum version as the second parameter and variant as the third parameter.

PackageLoader resolves dependencies and tell AssetsLoader to load the assets for all needed packages.

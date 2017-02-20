<?php

namespace Annotate\Packages\Loaders;

use Annotate\Diagnostics\CmsPanel;
use Annotate\Packages\Asset;
use Annotate\Packages\Exceptions\BadPackageVersionException;
use Annotate\Packages\Exceptions\PackageNotFoundException;
use Annotate\Packages\Exceptions\PackageVariantNotFoundException;
use Annotate\Packages\Package;
use Annotate\Packages\Parsers\BowerParser;
use Annotate\Packages\Parsers\NeonParser;
use Annotate\Themes\Theme;
use Kdyby\Events\Subscriber;
use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Tracy\Dumper;


class PackageLoader implements Subscriber
{

	/** @var Package[] */
	private $packages = [];

	/** @var AssetsLoader */
	private $assetsLoader;

	private $loadedPackages = [];

	/** @var string[] */
	private $directories = [];

	/** @var string */
	private $rootDir;

	private $loading = [];



	public function __construct($directories, $rootDir, AssetsLoader $assetsLoader)
	{
		foreach ($directories as $key => $directory) {
			if (is_dir($directory)) {
				$this->directories[] = realpath($directory);
			}
		}
		$this->rootDir = realpath($rootDir);
		$this->assetsLoader = $assetsLoader;
		$this->load();
	}



	public function load()
	{
		if (!$this->directories) {
			return;
		}

		$this->processNeon();
		$this->processJson();
		$this->addDebugSection();
	}



	private function processNeon()
	{
		$neonParser = new NeonParser(new NeonAdapter(), $this->rootDir);
		foreach (Finder::findFiles('*.package.neon')->from($this->directories) as $path => $file) {
			$package = $neonParser->parse($path);
			$this->packages[strtolower($package->getName())] = $package;
		}
	}



	private function processJson()
	{
		$dotBower = [];
		$bower = [];
		foreach (Finder::findFiles(['.bower.json', 'bower.json'])->from($this->directories) as $path => $file) {
			if (pathinfo($file, PATHINFO_BASENAME) === '.bower.json') {
				$dotBower[$path] = $file;
			} else {
				$bower[$path] = $file;
			}
		}

		foreach ($dotBower as $path => $file) {
			$this->processBowerFile($path);
		}

		foreach ($bower as $path => $file) {
			$this->processBowerFile($path);
		}
	}



	private function processBowerFile($path)
	{
		$jsonParser = new BowerParser($this->rootDir);
		$package = $jsonParser->parse($path);

		if (!isset($this->packages[strtolower($package->getName())])) {
			$this->packages[strtolower($package->getName())] = $package;
		}
	}



	private function addDebugSection()
	{
		$self = $this;
		CmsPanel::$sections[] = function () use ($self) {
			$packages = $self->loadedPackages;
			$html = '<h2>Loaded Packages:</h2>';
			$html .= '<div><table>';
			$html .= '<thead><tr><th>Name</th><th>Version</th><th>Variant</th><th>Deps</th></tr></thead>';
			foreach ($packages as $package) {
				$html .= '<tr><td>' . $package['name'] . '</td><td>' . $package['version'] . '</td><td>' . $package['variant'] . '</td><td>' . Dumper::toHtml(
						$package['dependencies'],
						[Dumper::COLLAPSE => TRUE]
					) . '</td></tr>';
			}
			$html .= '</table></div>';

			return $html;
		};
	}



	public function addDirectory($directory)
	{
		$this->directories[] = $directory;
		return $this;
	}



	public function getSubscribedEvents()
	{
		return [
			'Annotate\\Themes\\Loaders\\ThemesLoader::onActivateTheme'
		];
	}



	public function onActivateTheme(Theme $theme)
	{
		if ($theme->isChecked()) {
			return;
		}

		if (!$theme->hasDependencies()) {
			return;
		}

		foreach ($theme->getDependencies() as $name => $info) {
			if (!is_array($info)) {
				if (strpos($info, '#') !== FALSE) {
					$version = explode('#', $info)[1];
				} else {
					$version = $info;
				}
				$variant = 'default';
			} else {
				$version = isset($info['version']) ? $info['version'] : NULL;
				$variant = isset($info['variant']) ? $info['variant'] : 'default';
			}

			try {
				$this->loadPackage($name, $version, $variant);
			} catch (PackageNotFoundException $e) {
				throw new PackageNotFoundException('Theme cannot be loaded. Package "' . $name . '" does not exist.', 0, $e);
			} catch (BadPackageVersionException $e) {
				throw new BadPackageVersionException('Theme cannot be loaded. Theme requires "' . $name . '" version "' . $version . '"', 0, $e);
			}
		}
		$theme->setChecked();
	}



	public function loadPackage($name, $version = NULL, $packageVariant = 'default')
	{
		$name = strtolower($name);
		/** @var Package $package */
		$package = $this->getPackage($name, $version, $packageVariant);
		if ($package->isLoaded()) {
			return;
		}
		$this->loading[$name] = TRUE;
		if ($package->getDependencies()) {
			if (!$package->isChecked()) {
				foreach ($package->getDependencies() as $dep_name => $info) {
					if (!is_array($info)) {
						if (strpos($info, '#') !== FALSE) {
							$dep_version = explode('#', $info)[1];
						} else {
							$dep_version = $info;
						}
						$variant = 'default';
					} else {
						$dep_version = isset($info['version']) ? $info['version'] : NULL;
						$variant = isset($info['variant']) ? $info['variant'] : 'default';
					}
					if (!isset($this->loading[$dep_name])) {
						$this->loadPackage($dep_name, $dep_version, $variant);
					}
				}
				$package->setChecked();
			}
		} else {
			$package->setChecked();
		}
		$this->loadPackageAssets($packageVariant, $package);

		$this->loadedPackages[] = [
			'name' => $package->getName(),
			'version' => $package->getVersion(),
			'variant' => $packageVariant,
			'dependencies' => $package->getDependencies()
		];

		unset($this->loading[$name]);
		$package->setLoaded();
	}



	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @throws BadPackageVersionException
	 * @throws PackageVariantNotFoundException
	 * @throws PackageNotFoundException
	 * @return Package
	 */
	public function getPackage($name, $version = NULL, $variant = 'default')
	{
		$name = strtolower($name);
		if (!isset($this->packages[$name])) {
			throw new PackageNotFoundException('Package "' . $name . '" does not exist');
		}

		if (!$this->packages[$name]->hasVariant($variant)) {
			throw new PackageVariantNotFoundException('Package "' . $name . '" does not have variant "' . $variant . '"');
		}

		/* @var Package */
		$package = $this->packages[$name];

		if ($version) {
			$version = str_replace('~', NULL, $version);
			$version = str_replace('^', '>=', $version);
			$matches = Strings::match($version, '~(lt|<>|<=|le|>=|<|>|gt|ge|==|=|eq|!=|ne)*\s?(.+)~i');

			$versionNumber = $matches[2];
			$versionOperator = $matches[1];

			if (version_compare($package->getVersion(), $versionNumber, $versionOperator ?: '>=') === FALSE) {
				throw new BadPackageVersionException('Package "' . $name . '" is version "' . $package->getVersion() . '", but version "' . $version . '" required.');
			}
		}

		return $package;
	}



	/**
	 * @param          string
	 * @param  Package $package
	 *
	 * @return void
	 */
	private function loadPackageAssets($packageVariant, Package $package)
	{
		$variants = $package->getVariants();
		$requiredVariant = $variants[$packageVariant];

		if (isset($requiredVariant['scripts'])) {
			$scripts = [];
			foreach ($requiredVariant['scripts'] as $script) {
				$scripts[] = new Asset($package, $script);
			}
			$this->assetsLoader->addScripts($scripts);
		}

		if (isset($requiredVariant['styles'])) {
			$styles = [];
			foreach ($requiredVariant['styles'] as $style) {
				$styles[] = new Asset($package, $style);
			}
			$this->assetsLoader->addStyles($styles);
		}
	}



	/**
	 * @return Package[]
	 */
	public function getPackages()
	{
		return $this->packages;
	}

}

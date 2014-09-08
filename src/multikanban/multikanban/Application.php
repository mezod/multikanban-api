<?php

namespace multikanban\multikanban;

use Silex\Application as SilexApplication;
use Symfony\Component\Finder\Finder;

class Application extends SilexApplication
{
	public function __construct(array $values = array()){

        parent::__construct($values);

        $this->configureParameters();
        // $this->configureProviders();
        // $this->configureServices();
        // $this->configureSecurity();
        // $this->configureListeners();
    }

    private function configureParameters(){

        $this['root_dir'] = __DIR__.'/../../..';
        //$this['sqlite_path'] = $this['root_dir'].'/data/code_battles.sqlite';
    }

	/**
	 * Dynamically finds all *Controller.php files in the Controller directory,
	 * instantiates them, and mounts their routes.
	 *
	 * This is done so we can easily create new controllers without worrying
	 * about some of the Silex mechanisms to hook things together.
	 */
    public function mountControllers(){

        $controllerPath = 'src/multikanban/multikanban/Controller';
        $finder = new Finder();
        $finder->in($this['root_dir'].'/'.$controllerPath)
            ->name('*Controller.php')
        ;

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            // e.g. Api/FooController.php
            $cleanedPathName = $file->getRelativePathname();
            // e.g. Api\FooController.php
            $cleanedPathName = str_replace('/', '\\', $cleanedPathName);
            // e.g. Api\FooController
            $cleanedPathName = str_replace('.php', '', $cleanedPathName);

            $class = 'multikanban\\multikanban\\Controller\\'.$cleanedPathName;

            // don't instantiate the abstract base class
            $refl = new \ReflectionClass($class);
            if ($refl->isAbstract()) {
                continue;
            }

            $this->mount('/', new $class($this));
        }
    }
}
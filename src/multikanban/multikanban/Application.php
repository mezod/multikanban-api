<?php

namespace multikanban\multikanban;

use Silex\Application as SilexApplication;
use Symfony\Component\Finder\Finder;
use multikanban\multikanban\Repository\UserRepository;
use multikanban\multikanban\Repository\RepositoryContainer;

class Application extends SilexApplication
{
	public function __construct(array $values = array()){

        parent::__construct($values);

        $this->configureParameters();
        // $this->configureProviders();
        $this->configureServices();
        // $this->configureSecurity();
        // $this->configureListeners();
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

    private function configureParameters(){

        $this['root_dir'] = __DIR__.'/../../..';
        //$this['sqlite_path'] = $this['root_dir'].'/data/code_battles.sqlite';
    }

    private function configureServices()
    {
        $app = $this;

        $this['repository.user'] = $this->share(function() use ($app) {
            $repo = new UserRepository($app['db'], $app['repository_container']);
            //$repo->setEncoderFactory($app['security.encoder_factory']);

            return $repo;
        });
        // $this['repository.kanban'] = $this->share(function() use ($app) {
        //     return new KanbanRepository($app['db'], $app['repository_container']);
        // });
        // $this['repository.task'] = $this->share(function() use ($app) {
        //     return new TaskRepository($app['db'], $app['repository_container']);
        // });
        // $this['repository.stats'] = $this->share(function() use ($app) {
        //     return new StatsRepository($app['db'], $app['repository_container']);
        // });
        // $this['repository.api_token'] = $this->share(function() use ($app) {
        //     return new ApiTokenRepository($app['db'], $app['repository_container']);
        // });

        $this['repository_container'] = $this->share(function() use ($app) {
            return new RepositoryContainer($app, array(
                'user' => 'repository.user',
                // 'programmer' => 'repository.programmer',
                // 'project' => 'repository.project',
                // 'battle' => 'repository.battle',
                // 'api_token' => 'repository.api_token',
            ));
        });

        // $this['annotation_reader'] = $this->share(function() {
        //     return new AnnotationReader();
        // });

        // $this['api.validator'] = $this->share(function() use ($app) {
        //     return new ApiValidator($app['validator']);
        // });
    }
}
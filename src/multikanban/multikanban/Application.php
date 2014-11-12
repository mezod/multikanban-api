<?php

namespace multikanban\multikanban;

use Silex\Application as SilexApplication;
use Symfony\Component\Finder\Finder;

//Providers
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;

//Services
use multikanban\multikanban\Repository\UserRepository;
use multikanban\multikanban\Repository\KanbanRepository;
use multikanban\multikanban\Repository\TaskRepository;
use multikanban\multikanban\Repository\StatsRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use multikanban\multikanban\Validator\ApiValidator;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;

//Listeners
use multikanban\multikanban\Api\ApiProblem;
use multikanban\multikanban\Api\ApiProblemException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;





class Application extends SilexApplication
{
	public function __construct(array $values = array()){

        parent::__construct($values);

        $this->configureParameters();
        $this->configureProviders();
        $this->configureServices();
        $this->configureSecurity();
        $this->configureListeners();
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

    private function configureProviders()
    {
        // Doctrine DBAL
        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
            'dbname' => 'multikanban',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
            ),
        ));

        // Validation
        $this->register(new ValidatorServiceProvider());
        // configure validation to load from a YAML file
        $this['validator.mapping.class_metadata_factory'] = $this->share(function() {
            return new ClassMetadataFactory(
                new AnnotationLoader($this['annotation_reader'])
            );
        });
    }

    private function configureServices()
    {
        $app = $this;

        $this['repository.user'] = $this->share(function() use ($app) {
            $repo = new UserRepository($app['db']);
            $repo->setEncoderFactory($app['security.encoder_factory']);

            return $repo;
        });
        $this['repository.kanban'] = $this->share(function() use ($app) {
            return new KanbanRepository($app['db']);
        });
        $this['repository.task'] = $this->share(function() use ($app) {
            return new TaskRepository($app['db']);
        });
        $this['repository.stats'] = $this->share(function() use ($app) {
            return new StatsRepository($app['db']);
        });
        // $this['repository.api_token'] = $this->share(function() use ($app) {
        //     return new ApiTokenRepository($app['db']);
        // });

        $this['annotation_reader'] = $this->share(function() {
            return new AnnotationReader();
        });

        $this['api.validator'] = $this->share(function() use ($app) {
            return new ApiValidator($app['validator']);
        });
    }

    private function configureSecurity(){

        $app = $this;

        $this->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'main' => array(
                    'pattern' => '^/',
                    'form' => true,
                    'users' => $this->share(function () use ($app) {
                        return $app['repository.user'];
                    }),
                    'anonymous' => true,
                    'logout' => true,
                ),
            )
        ));
    }

    private function configureListeners(){

        $app = $this;

        $this->error(function(\Exception $e, $statusCode) use ($app){

            // if(strpos($app['request']->getPathInfo(), '/api') !== 0){
            //     return;
            // }
            
            if($app['debug'] && $statusCode === 500){
                return;
            }

            if($e instanceof ApiProblemException){
                $apiProblem = $e->getApiProblem();
            } else {
                $apiProblem = new ApiProblem($statusCode);

                if($e instanceof HttpException){
                    $apiProblem->set('detail', $e->getMessage());
                }
            }

            $data = $apiProblem->toArray();
            if($data['type'] != 'about:blank'){
                $data['type'] = 'http://localhost:8000/docs/errors#'.$data['type'];
            }

            $response = new JsonResponse(
                $data,
                $apiProblem->getStatusCode()
            );
            
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        });
        
    }
}
<?php

namespace multikanban\multikanban;

use Silex\Application as SilexApplication;
use Symfony\Component\Finder\Finder;

//Providers
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

//Services
use multikanban\multikanban\Repository\UserRepository;
use multikanban\multikanban\Repository\KanbanRepository;
use multikanban\multikanban\Repository\TaskRepository;
use multikanban\multikanban\Repository\StatsRepository;
use multikanban\multikanban\Repository\RepositoryContainer;
use Doctrine\Common\Annotations\AnnotationReader;
use multikanban\multikanban\Validator\ApiValidator;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use multikanban\multikanban\Api\ApiProblemResponseFactory;

//Security
use multikanban\multikanban\Security\Authentication\ApiEntryPoint;
use multikanban\multikanban\Security\Authentication\ApiTokenListener;
use multikanban\multikanban\Security\Authentication\ApiTokenProvider;
use multikanban\multikanban\Security\Http\EntryPoint\BasicAuthenticationEntryPoint;

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
            'password' => 'root',
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

        // Translation
        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array('en'),
        ));
        $this['translator'] = $this->share($this->extend('translator', function($translator) {
            /** @var \Symfony\Component\Translation\Translator $translator */
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', $this['root_dir'].'/translations/en.yml', 'en');

            return $translator;
        }));
    }

    private function configureServices()
    {
        $app = $this;

        $this['repository.user'] = $this->share(function() use ($app) {
            $repo = new UserRepository($app['db'], $app['repository_container']);
            $repo->setEncoderFactory($app['security.encoder_factory']);

            return $repo;
        });
        $this['repository.kanban'] = $this->share(function() use ($app) {
            return new KanbanRepository($app['db'], $app['repository_container']);
        });
        $this['repository.task'] = $this->share(function() use ($app) {
            return new TaskRepository($app['db'], $app['repository_container']);
        });
        $this['repository.stats'] = $this->share(function() use ($app) {
            return new StatsRepository($app['db'], $app['repository_container']);
        });
        $this['repository_container'] = $this->share(function() use ($app) {
            return new RepositoryContainer($app, array(
                'user' => 'repository.user',
                'kanban' => 'repository.kanban',
                'task' => 'repository.task',
                'stats' => 'repository.stats'
            ));
        });

        $this['annotation_reader'] = $this->share(function() {
            return new AnnotationReader();
        });

        $this['api.validator'] = $this->share(function() use ($app) {
            return new ApiValidator($app['validator']);
        });

        $this['serializer'] = $this->share(function() use ($app) {
            return \JMS\Serializer\SerializerBuilder::create()
                ->setCacheDir($app['root_dir'].'/cache/serializer')
                ->setDebug($app['debug'])
                ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
                ->build();
        });

        $this['api.response_factory'] = $this->share(function() {
            return new ApiProblemResponseFactory();
        });
    }

    private function configureSecurity(){

        $app = $this;

        $this->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'main' => array(
                    'pattern' => '^/',
                    'users' => $this->share(function () use ($app) {
                        return $app['repository.user'];
                    }),
                    'anonymous' => true,
                    'logout' => true,
                    'stateless' => true,
                    'api_token' => true,
                    'http' => true
                )
            )
        ));

        // require login for application management
        $this['security.access_rules'] = array(
            // allow anonymous API - if auth is needed, it's handled in the controller
            array('^/', 'IS_AUTHENTICATED_ANONYMOUSLY')
        );

        // setup our custom API token authentication
        $app['security.authentication_listener.factory.api_token'] = $app->protect(function ($name, $options) use ($app) {

            // the class that reads the token string off of the Authorization header
            $app['security.authentication_listener.'.$name.'.api_token'] = $app->share(function () use ($app) {
                return new ApiTokenListener($app['security'], $app['security.authentication_manager']);
            });

            // the class that looks up the ApiToken object in the database for the given token string
            // and authenticates the user if it's found
            $app['security.authentication_provider.'.$name.'.api_token'] = $app->share(function () use ($app) {
                return new ApiTokenProvider($app['repository.user']);
            });

            // the class that decides what should happen if no authentication credentials are passed
            $this['security.entry_point.'.$name.'.api_token'] = $app->share(function() use ($app) {
                return new ApiEntryPoint($app['translator'], $app['api.response_factory']);
            });

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.api_token',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.api_token',
                // the entry point id
                'security.entry_point.'.$name.'.api_token',
                // the position of the listener in the stack
                'pre_auth'
            );
        });

        $this['security.entry_point.main.http'] = $this->share(function() {
            return new BasicAuthenticationEntryPoint('main');
        });
    }

    private function configureListeners(){

        $app = $this;

        $this->error(function(\Exception $e, $statusCode) use ($app) {
        
            // allow 500 errors in debug to be thrown
            if ($app['debug'] && $statusCode == 500) {
                return;
            }

            if ($e instanceof ApiProblemException) {
                $apiProblem = $e->getApiProblem();
            } else {
                $apiProblem = new ApiProblem(
                    $statusCode
                );

                /*
                 * If it's an HttpException message (e.g. for 404, 403),
                 * we'll say as a rule that the exception message is safe
                 * for the client. Otherwise, it could be some sensitive
                 * low-level exception, which should *not* be exposed
                 */
                if ($e instanceof HttpException) {
                    $apiProblem->set('detail', $e->getMessage());
                }
            }

            /** @var \KnpU\CodeBattle\Api\ApiProblemResponseFactory $factory */
            $factory = $app['api.response_factory'];

            return $factory->createResponse($apiProblem);
        });
        
    }
}
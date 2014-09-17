<?php

namespace multikanban\multikanban\Controller;

use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use multikanban\multikanban\Application;
use multikanban\multikanban\Model\User;
use multikanban\multikanban\Model\Kanban;
use multikanban\multikanban\Model\Task;
use multikanban\multikanban\Model\Stats;
// use multikanban\multikanban\Repository\UserRepository;
// use multikanban\multikanban\Repository\ProgrammerRepository;
// use multikanban\multikanban\Repository\ProjectRepository;
// use multikanban\multikanban\Security\Token\ApiTokenRepository;

/**
 * Base controller class to hide Silex-related implementation details
 */
abstract class BaseController implements ControllerProviderInterface
{
    /**
     * @var \multikanban\multikanban\Application
     */
    protected $container;

    public function __construct(Application $app)
    {
        $this->container = $app;
    }

    abstract protected function addRoutes(ControllerCollection $controllers);

    public function connect(SilexApplication $app){
        
        $controllers = $app['controllers_factory'];

        $this->addRoutes($controllers);

        return $controllers;
    }

 

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->container['repository.user'];
    }

    /**
     * @return KanbanRepository
     */
    protected function getKanbanRepository()
    {
        return $this->container['repository.kanban'];
    }

    /**
     * @return TaskRepository
     */
    protected function getTaskRepository()
    {
        return $this->container['repository.task'];
    }

    /**
     * @return StatsRepository
     */
    protected function getStatsRepository()
    {
        return $this->container['repository.stats'];
    }

}

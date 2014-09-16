<?php

namespace multikanban\multikanban\Repository;

use multikanban\multikanban\Model\Stats;
use Doctrine\DBAL\Connection;

class StatsRepository{

    protected $connection;

    public function __construct(Connection $connection){        

        $this->connection = $connection;
    }

    public function getAppStats(){

    	$stats = new Stats();

    	// number of users
    	$sql = "SELECT COUNT(*) FROM user";
        $stats->numberUsers = $this->connection->fetchColumn($sql);
    	
        // number of kanbans
        $sql = "SELECT COUNT(*) FROM kanban";
        $stats->numberKanbans = $this->connection->fetchColumn($sql);

        // number of tasks
        $sql = "SELECT COUNT(*) FROM task";
        $stats->numberTasks = $this->connection->fetchColumn($sql);

        // number of completed (state is either done or archive) tasks
        $sql = "SELECT COUNT(*) FROM task WHERE state = 'done' OR state = 'archive'";
        $stats->numberCompletedTasks = $this->connection->fetchColumn($sql);
    
    	return $stats;	
    }

    public function getUserStats($user_id){

    	$stats = new Stats();
    	
        // number of kanbans of user user_id
        $sql = "SELECT COUNT(*) FROM kanban WHERE user_id = ?";
        $stats->numberKanbans = $this->connection->fetchColumn($sql, array((int) $user_id));

        // number of tasks of user user_id
        $sql = "SELECT COUNT(*) FROM task WHERE user_id = ?";
        $stats->numberTasks = $this->connection->fetchColumn($sql, array((int) $user_id));

        // number of completed (state is either done or archive) tasks of user user_id
        $sql = "SELECT COUNT(*) FROM task WHERE user_id = ? AND (state = 'done' OR state = 'archive')";
        $stats->numberCompletedTasks = $this->connection->fetchColumn($sql, array((int) $user_id));
    
    	return $stats;	
    }

    public function getKanbanStats($kanban_id){

    	$stats = new Stats();

        // number of tasks of kanban kanban_id
        $sql = "SELECT COUNT(*) FROM task WHERE kanban_id = ?";
        $stats->numberTasks = $this->connection->fetchColumn($sql, array((int) $kanban_id));

        // number of completed (state is either done or archive) tasks of kanban kanban_id
        $sql = "SELECT COUNT(*) FROM task WHERE kanban_id = ? AND (state = 'done' OR state = 'archive')";
        $stats->numberCompletedTasks = $this->connection->fetchColumn($sql, array((int) $kanban_id));
    
    	return $stats;	
    }
}
<?php

namespace multikanban\multikanban\Repository;

use multikanban\multikanban\Model\Task;
use Doctrine\DBAL\Connection;

class TaskRepository{

	protected $connection;

    public function __construct(Connection $connection){        

        $this->connection = $connection;
    }

    protected function getClassName()
    {
        return 'multikanban\multikanban\Model\Task';
    }

    protected function getTableName()
    {
        return 'task';
    }


    public function save($task)
    {

        $data = array();
        foreach($task as $key => $value){
            $data[$key] = $value;
        }

        $this->connection->insert('task', $data);

        return $this->connection->lastInsertId();
    }

    public function findAll($kanban_id){

        $sql = "SELECT * FROM task WHERE kanban_id = ?";
        $tasks = $this->connection->fetchAll($sql, array((int) $kanban_id));
        
        $taskArray = array();

        foreach($tasks as $eachTask){
            $task = new Task();
            foreach ($eachTask as $key => $value){
                $task->$key = $value;
            }
            array_push($taskArray, $task);
        }

        return $taskArray;
    }

    public function findCompleted($user_id){

    	// ITERATE: GET KANBAN DATA

        $sql = "SELECT * FROM task WHERE user_id = ? AND (state = 'done' OR state = 'archive')";
        $tasks = $this->connection->fetchAll($sql, array((int) $user_id));
        
        $taskArray = array();

        foreach($tasks as $eachTask){
            $task = new Task();
            foreach ($eachTask as $key => $value){
                $task->$key = $value;
            }
            array_push($taskArray, $task);
        }

        return $taskArray;
    }

    public function findOneById($id){

        $sql = "SELECT * FROM task WHERE id = ?";
        $post = $this->connection->fetchAssoc($sql, array((int) $id));
        
        if(!$post) return false;

        $task = new Task();

        foreach ($post as $key => $value){
            $task->$key = $value;
        }

        return $task;
    }

    public function update($task){

    	//ITERATE: Position/State change? Update changes for the rest

        $data = array();
        foreach($task as $key => $value){
            $data[$key] = $value;
        }

        $this->connection->update('task', $data, array('id' => $data['id']));
    }

    public function delete($task){

    	//ITERATE: deletion means position change? Update changes for the rest

        $this->connection->delete('task', array('id' => $task->id));
    }
}
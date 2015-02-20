<?php

namespace multikanban\multikanban\Repository;

use multikanban\multikanban\Model\Kanban;
use Doctrine\DBAL\Connection;

class KanbanRepository{

    protected $connection;

    public function __construct(Connection $connection){        

        $this->connection = $connection;
    }

    protected function getClassName()
    {
        return 'multikanban\multikanban\Model\Kanban';
    }

    protected function getTableName()
    {
        return 'kanban';
    }


    public function save($kanban)
    {

        $data = array();
        foreach($kanban as $key => $value){
            $data[$key] = $value;
        }

        $this->connection->insert('kanban', $data);

        return $this->connection->lastInsertId();
    }

    public function getKanbanPosition($user_id){

        $sql = "SELECT COUNT(*) FROM kanban WHERE user_id = ?";
        $position = $this->connection->fetchColumn($sql, array((int) $user_id));
        return $position;
    }

    public function findAll($user_id){

        $sql = "SELECT * FROM kanban WHERE user_id = ?";
        $kanbans = $this->connection->fetchAll($sql, array((int) $user_id));
        
        $kanbanArray = array();

        foreach($kanbans as $eachKanban){
            $kanban = new Kanban();
            foreach ($eachKanban as $key => $value){
                if (is_numeric($value)) {
                    $value = (float) $value;
                }
                $kanban->$key = $value;
            }
            array_push($kanbanArray, $kanban);
        }

        return $kanbanArray;
    }

    public function findOneById($id){

        $sql = "SELECT * FROM kanban WHERE id = ?";
        $post = $this->connection->fetchAssoc($sql, array((int) $id));
        
        if(!$post) return false;

        $kanban = new Kanban();

        foreach ($post as $key => $value){
            if (is_numeric($value)) {
                $value = (float) $value;
            }
            $kanban->$key = $value;
        }

        return $kanban;
    }

    public function update($kanban){

        $data = array();
        foreach($kanban as $key => $value){
            $data[$key] = $value;
        }

        $this->connection->update('kanban', $data, array('id' => $data['id']));
    }

    public function updatePositions($user_id, $oldPosition, $newPosition){

        if($oldPosition < $newPosition){
            $sql = "UPDATE kanban SET position = position - 1 WHERE user_id = ? AND position > ? AND position <= ?";
            $this->connection->executeQuery($sql, array((int) $user_id, (int) $oldPosition, (int) $newPosition));
        }else{
            $sql = "UPDATE kanban SET position = position + 1 WHERE user_id = ? AND position < ? AND position >= ?";
            $this->connection->executeQuery($sql, array((int) $user_id, (int) $oldPosition, (int) $newPosition));
        }
    }

    public function delete($kanban){

        $this->connection->delete('kanban', array('id' => $kanban->id));
    }

    public function updatePositionsDelete($user_id, $position){

        $sql = "UPDATE kanban SET position = position - 1 WHERE user_id = ? AND position > ?";
        $this->connection->executeQuery($sql, array((int) $user_id, (int) $position));
    }
}

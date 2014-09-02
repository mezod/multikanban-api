<?php

namespace multikanban\multikanban\Model;

class Task
{
    /* All public properties are persisted */
    public $id;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Kanban
     */
    public $kanban;

    public $text;

    public $dateCreated;

    public $dateCompleted;

    public $position;

    public $column;
}

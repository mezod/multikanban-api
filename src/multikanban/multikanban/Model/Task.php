<?php

namespace multikanban\multikanban\Model;

class Task
{
    /* All public properties are persisted */
    public $id;

    /**
     * @var User
     */
    public $user_id;

    /**
     * @var Kanban
     */
    public $kanban_id;

    public $text;

    public $dateCreated;

    public $dateCompleted;

    public $position;

    public $state;
}

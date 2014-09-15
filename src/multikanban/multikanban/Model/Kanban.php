<?php

namespace multikanban\multikanban\Model;

class Kanban
{
    /* All public properties are persisted */
    public $id;

    /**
     * @var User
     */
    public $user_id;

    public $title;

    public $dateCreated;

    public $lastEdited;

    public $position;
}

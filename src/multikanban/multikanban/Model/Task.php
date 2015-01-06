<?php

namespace multikanban\multikanban\Model;

use Symfony\Component\Validator\Constraints as Assert;

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

    /**
    * @Assert\NotBlank(message="Text cannot be empty.")
    */
    public $text;

    public $dateCreated;

    public $dateCompleted;

    /**
    * @Assert\NotBlank(message="Position cannot be empty.")
    */
    public $position;

    /**
    * @Assert\NotBlank(message="State cannot be empty.")
    */
    public $state;
}

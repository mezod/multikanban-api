<?php

namespace multikanban\multikanban\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Kanban
{
    /* All public properties are persisted */
    public $id;

    /**
     * @var User
     */
    public $user_id;

    /**
    * @Assert\NotBlank(message="Title cannot be empty.")
    */
    public $title;

    public $slug;

    public $dateCreated;

    public $lastEdited;

    /**
    * @Assert\NotBlank(message="Position cannot be empty.")
    */
    public $position;
}

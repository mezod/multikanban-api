<?php

namespace multikanban\multikanban\Security\Token;

use Symfony\Component\Validator\Constraints as Assert;

class ApiToken
{

    /* All public properties are persisted */
    public $id;

    public $token;

    /**
     * @Assert\NotBlank(message="Please add some notes about this token")
     */
    public $notes;

    public $user_id;

    /**
     * @var \DateTime
     */
    public $createdAt;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
        $this->createdAt = new \DateTime();
        $this->token = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}

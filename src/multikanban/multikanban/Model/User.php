<?php

namespace multikanban\multikanban\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{

    /* All public properties are persisted */
    public $id;

    public $username;

    public $password;

    public $email;

    public $registered;

    public $numberKanbans;

    public $numberTasks;

    public $numberCompletedTasks;

    /* non-persisted properties */
    private $plainPassword;

    public function __construct($username = null, $password = null, $email = null){

        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
    }

    /**
     * Start: Security-related stuff
     */
    public function getUsername()
    {
        return $this->email;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function getRegistered()
    {
        return $this->registered;
    }

    public function getNumberKanbans()
    {
        return $this->numberKanbans;
    }

    public function getNumberTasks()
    {
        return $this->numberTasks;
    }
    
    public function getNumberCompletedTasks()
    {
        return $this->numberCompletedTasks;
    }


    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }
    
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    // ABSTRACT METHODS
    public function eraseCredentials()
    {
        $this->password = null;
    }
    public function getRoles()
    {
        return array('ROLE_USER');
    }
    public function getSalt()
    {
        return null;
    }
}

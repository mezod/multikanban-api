<?php

namespace multikanban\multikanban\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{

    /* All public properties are persisted */
    public $id;

    public $email;

    public $password;

    public $username;

    public $registered;

    public $numberKanbans;

    public $numberTasks;

    public $numberCompletedTasks;

    /* non-persisted properties */
    private $plainPassword;

    /**
     * Start: Security-related stuff
     */
    public function getUsername()
    {
        return $this->email;
    }
    public function eraseCredentials()
    {
        $this->password = null;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getRoles()
    {
        return array('ROLE_USER');
    }
    public function getSalt()
    {
        return null;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }
    public function getPlainPassword()
    {
        return $this->plainPassword;
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

}

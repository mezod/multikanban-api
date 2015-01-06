<?php

namespace multikanban\multikanban\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class User implements UserInterface
{

    /* All public properties are persisted */

    /**
     * @Serializer\Expose()
     */
    public $id;

    /**
     * @Assert\NotBlank(message="Username cannot be empty.")
     * @Assert\Length(
     *      min = 3,
     *      max = 16,
     *      minMessage = "The username must be at least {{ limit }} characters long",
     *      maxMessage = "The username cannot be longer than {{ limit }} characters long"
     * )
     * @Serializer\Expose()
     */
    public $username;

    /**
     * @Assert\NotBlank(message="Password cannot be empty.")
     * @Assert\Length(
     *      min = 8,
     *      max = 26,
     *      minMessage = "The password must be at least {{ limit }} characters long",
     *      maxMessage = "The password cannot be longer than {{ limit }} characters long"
     * )
     */
    public $password;

    /**
     * @Assert\NotBlank(message="Email cannot be empty.")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     * @Serializer\Expose()
     */
    public $email;

    /**
     * @Serializer\Expose()
     */
    public $registered;

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

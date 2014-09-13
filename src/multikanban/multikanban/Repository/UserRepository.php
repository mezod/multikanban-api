<?php

namespace multikanban\multikanban\Repository;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use multikanban\multikanban\Model\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserRepository extends BaseRepository implements UserProviderInterface
{
    /**
     * Injected via setter injection
     *
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    protected $connection;

    public function __construct(Connection $connection){        

        $this->connection = $connection;
    }

    protected function getClassName()
    {
        return 'multikanban\multikanban\Model\User';
    }

    protected function getTableName()
    {
        return 'user';
    }

    /**
     * Overridden to encode the password
     *
     * @param $obj
     */
    public function save($user)
    {
        /** @var User $user */
        if ($user->getPlainPassword()) {
            $user->password = $this->encodePassword($user, $user->getPlainPassword());
        }

        $data = array();
        foreach($user as $key => $value){
            $data[$key] = $value;
        }

        $this->connection->insert('user', $data);
    }

    public function findAll(){

        $sql = "SELECT * FROM user";
        $users = $this->connection->fetchAll($sql);
        
        $userArray = array();

        foreach($users as $eachUser){
            $user = new User();
            foreach ($eachUser as $key => $value){
                $user->$key = $value;
            }
            array_push($userArray, $user);
        }

        return $userArray;
    }

    public function findOneById($id){

        $sql = "SELECT * FROM user WHERE id = ?";
        $post = $this->connection->fetchAssoc($sql, array((int) $id));
        
        $user = new User();

        foreach ($post as $key => $value){
            $user->$key = $value;
        }

        return $user;
    }

    public function findOneByUsername($username){

        $sql = "SELECT * FROM user WHERE username = ?";
        $post = $this->connection->fetchAssoc($sql, array($username));
        
        $user = new User();

        foreach ($post as $key => $value){
            $user->$key = $value;
        }

        return $user;
    }

    public function update($user){

        if ($user->getPlainPassword()) {
            $user->password = $this->encodePassword($user, $user->getPlainPassword());
        }

        $data = array();
        foreach($user as $key => $value){
            $data[$key] = $value;
        }

        $this->connection->update('user', $data, array('id' => $data['id']));
    }

    public function delete($user){

        $this->connection->delete('user', array('id' => $user->id));
    }








    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        // allow login by email too
        if (!$user) {
            $user = $this->findUserByEmail($username);
        }

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Email "%s" does not exist.', $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'multikanban\multikanban\Model\User';
    }

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     */
    public function setEncoderFactory($encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    private function encodePassword(User $user, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($user);

        // compute the encoded password for foo
        return $encoder->encodePassword($password, $user->getSalt());
    }
}

<?php

namespace multikanban\multikanban\Security\Token;

use multikanban\multikanban\Repository\BaseRepository;
use multikanban\multikanban\Model\User;

class ApiTokenRepository extends BaseRepository
{
    const TABLE_NAME = 'ApiToken';

    protected function getClassName()
    {
        return 'multikanban\multikanban\Security\Token\ApiToken';
    }

    protected function getTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @param $token
     * @return ApiToken
     */
    public function findOneByToken($token)
    {
        return $this->findOneBy(array('token' => $token));
    }

    public function findAllForUser(User $user)
    {
        return $this->findAllBy(array('user_id' => $user->id));
    }

    protected function finishHydrateObject($obj)
    {
        $this->normalizeDateProperty('createdAt', $obj);
    }


    public function findOneById($id)
    {
        return $this->findOneBy(array('user_id' => $id));
    }

    /**
     * Overridden to create our ApiToken even though it has a constructor arg
     *
     * @param string $class
     * @param array $data
     * @return ApiToken
     */
    protected function createObject($class, array $data)
    {
        return new $class($data['user_id']);
    }
} 
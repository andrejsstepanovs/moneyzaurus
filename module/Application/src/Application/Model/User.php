<?php

namespace Application\Model;

use Varient\Database\Model\AbstractModel;

/**
 * @method User setUserId(integer $userId)
 * @method integer getUserId()
 * @method User setUsername(string $username)
 * @method string getUsername()
 * @method User setEmail(string $email)
 * @method string getEmail()
 * @method User setDisplayName(string $displayName)
 * @method string getDisplayName()
 * @method User setPassword(string $password)
 * @method string getPassword()
 * @method User setState(string $state)
 * @method string getState()
 */
class User extends AbstractModel
{
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * @param integer $id
     * @return \Application\Model\User
     */
    public function setId($id)
    {
        return $this->setUserId($id);
    }
}

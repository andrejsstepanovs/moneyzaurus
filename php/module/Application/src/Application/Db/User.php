<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class User
 *
 * @package Application\Db
 *
 * @method \Application\Db\User setUserId(int $userId)
 * @method \Application\Db\User setRole(string $role)
 * @method \Application\Db\User setUsername(string $username)
 * @method \Application\Db\User setEmail(string $email)
 * @method \Application\Db\User setDisplayName(string $displayName)
 * @method \Application\Db\User setPassword(string $password)
 * @method \Application\Db\User setState(int $state)
 * @method int    getUserId()
 * @method string getRole()
 * @method string getUsername()
 * @method string getEmail()
 * @method string getDisplayName()
 * @method string getPassword()
 * @method int    getState()
 * @method \Application\Db\User unsPassword()
 */
class User extends ActiveRecord
{
    /** state when user is active */
    const STATE_ACTIVE  = 1;

    /** state when user is deleted */
    const STATE_DELETED = 0;

    /** user group */
    const GROUP_USER = 'user';

    /** adming group */
    const GROUP_ADMIN = 'admin';

    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $shema
     */
    public function __construct($tableName = 'user', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}

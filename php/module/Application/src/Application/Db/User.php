<?php

namespace Application\Db;

use Db\ActiveRecord;

/**
 * Class User
 *
 * @package Application\Db
 *
 * @method User setUserId(int $userId)
 * @method User setRole(string $role)
 * @method User setUsername(string $username)
 * @method User setEmail(string $email)
 * @method User setDisplayName(string $displayName)
 * @method User setPassword(string $password)
 * @method User setState(int $state)
 * @method int    getUserId()
 * @method string getRole()
 * @method string getUsername()
 * @method string getEmail()
 * @method string getDisplayName()
 * @method string getPassword()
 * @method int    getState()
 * @method User unsPassword()
 * @method User load()
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
     * @param string|null                   $schema
     */
    public function __construct($tableName = 'user', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}

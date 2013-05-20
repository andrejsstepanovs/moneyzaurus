<?php

namespace Application\Authentication;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Db\Adapter\Adapter as DbAdapter;

class Adapter implements AuthAdapter
{
    public function __construct(
        DbAdapter $zendDb,
        $tableName = 'user',
        $identityColumn = 'email',
        $credentialColumn = 'password',
        $credentialTreatment = null)
    {
        // ...
    }

    /**
     * Returns the identity of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getIdentity()
    {

    }

    /**
     * Sets the identity for binding
     *
     * @param  mixed                       $identity
     * @return ValidatableAdapterInterface
     */
    public function setIdentity($identity)
    {

    }

    /**
     * Returns the credential of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getCredential()
    {

    }

    /**
     * Sets the credential for binding
     *
     * @param  mixed                       $credential
     * @return ValidatableAdapterInterface
     */
    public function setCredential($credential)
    {

    }
}

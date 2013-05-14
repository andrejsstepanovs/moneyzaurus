<?php
namespace Application\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;

class Connection extends AbstractDbMapper
{
    protected $tableName  = 'connection';

    public function findById($id)
    {
        $select = $this->getSelect()
                       ->where(array('connection_id' => $id));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        $result = parent::insert($entity, $tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'connection_id = ' . $entity->getId();
        }

        return parent::update($entity, $where, $tableName, $hydrator);
    }
}
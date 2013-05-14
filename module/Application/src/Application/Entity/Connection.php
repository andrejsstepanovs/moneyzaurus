<?php
namespace Application\Entity;

class Connection
{
    private $id;
    private $id_user;
    private $id_user_parent;
    private $date_created;


    public function setId($id) {
        $this->id = (int) $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function setIdUser($idUser) {
        $this->id_user = $idUser;
        return $this;
    }

    public function getIdUserParent() {
        return $this->id_user_parent;
    }

    public function setIdUserParent($idUserParent) {
        $this->id_user_parent = $idUserParent;
        return $this;
    }

    public function getDateCreated() {
        return $this->date_created;
    }

    public function setDateCreated($dateCreated) {
        $this->date_created = $dateCreated;
        return $this;
    }

}
<?php
namespace Moneyzaurus\Entity;

class Transaction
{
    protected $id;
    protected $id_user;
    protected $id_group;
    protected $id_item;
    protected $price;
    protected $id_currency;
    protected $date;
    protected $date_created;


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

    public function getIdGroup() {
        return $this->id_group;
    }

    public function setIdGroup($idGroup) {
        $this->id_group = $idGroup;
        return $this;
    }

    public function getIdItem() {
        return $this->id_item;
    }

    public function setIdItem($idItem) {
        $this->id_item = $idItem;
        return $this;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

    public function getCurrency() {
        return $this->id_currency;
    }

    public function setCurrency($idCurrency) {
        $this->id_currency = $idCurrency;
        return $this;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
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
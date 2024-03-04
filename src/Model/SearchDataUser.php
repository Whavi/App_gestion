<?php

namespace App\Model;

class SearchDataUser
{
    /** @var int */
    public $page = 1;

    /** @var string */
    public string $nom = '';

    public string $prenom = '';

    public string $email = '';

    public function getRecherche()
    {
        return $this->nom;
    }

}
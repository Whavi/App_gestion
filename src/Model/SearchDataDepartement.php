<?php

namespace App\Model;

class SearchDataDepartement
{
    /** @var int */
    public $page = 1;

    /** @var string */
    public string $nom = '';
    public function getRecherche()
    {
        return $this->nom;
    }
}
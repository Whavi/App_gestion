<?php

namespace App\Model;

class SearchDataProduct
{
    /** @var int */
    public $page = 1;

    /** @var string */
    public string $nom = '';

    public string $identifiant = '';

    public string $ref = '';

    public function getRecherche()
    {
        return $this->ref;
    }

}
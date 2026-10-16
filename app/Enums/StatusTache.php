<?php

namespace App\Enums;

enum StatusTache: string
{
    case AFaire = 'a_faire';
    case EnCours = 'en_cours';
    case Terminee = 'terminee';
    case Annule = 'annule';
}

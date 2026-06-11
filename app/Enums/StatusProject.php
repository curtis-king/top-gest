<?php

namespace App\Enums;

enum StatusProject: string
{
    case Actif = 'actif';
    case EnCours = 'en_cours';
    case Termine = 'termine';
    case Annule = 'annule';
}

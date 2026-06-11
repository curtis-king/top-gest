<?php

namespace App\Enums;

enum StatusDossier: string
{
    case Actif = 'actif';
    case Suspendu = 'suspendu';
    case Termine = 'termine';
    case Annule = 'annule';
}

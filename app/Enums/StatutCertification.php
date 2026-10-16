<?php

namespace App\Enums;

enum StatutCertification: string
{
    case NonCertifiee = 'non_certifiee';
    case Certifiee = 'certifiee';
    case Echec = 'echec';
}

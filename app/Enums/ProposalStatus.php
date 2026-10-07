<?php
namespace App\Enums;

enum ProposalSatuts :string{
    case PENDING='pending';
    case ACCEPTED ='accepted';
    case REJECTED ='rejected';
}

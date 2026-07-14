<?php
namespace App\Enums;

enum ProjectStatus :string{
    case OPEN ='open';
    case IN_PROGRESS='in_progress';
    case COMPLETED ='completed';
    case EXPIRED='expired';
}

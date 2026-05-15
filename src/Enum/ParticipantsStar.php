<?php

namespace App\Enum;

enum ParticipantsStar: string
{
    case TERRIBLE = '0';
    case AWFUL = '0.5';
    case VERY_BAD = '1';
    case BAD = '1.5';
    case NOT_BAD = '2';
    case BELOW_AVERAGE = '2.5';
    case AVERAGE = '3';
    case GOOD = '3.5';
    case VERY_GOOD = '4';
    case EXCELLENT = '4.5';
    case OUTSTANDING = '5';

    /**
     * Pomocná metoda, převod na float
     */
    public function getNumber(): float
    {
        return (float) $this->value;
    }
}
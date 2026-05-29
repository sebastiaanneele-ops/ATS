<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AVG / Retentie
    |--------------------------------------------------------------------------
    |
    | Aantal dagen dat een sollicitatie bewaard blijft (geteld vanaf de
    | ontvangstdatum). Daarna worden persoonsgegevens geanonimiseerd en het
    | cv verwijderd. De geanonimiseerde records blijven bestaan voor statistiek.
    | Richtlijn NVP: ~4 weken zonder toestemming, tot 1 jaar met toestemming.
    |
    */

    'retention_days' => (int) env('ATS_RETENTION_DAYS', 365),

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modo de integración ERP
    |--------------------------------------------------------------------------
    |
    | Determina si la instalación opera con datos propios (propio) o integrada
    | con Tango (tango). Afecta vistas, SP y providers de lectura/escritura
    | para artículos, operarios y otros maestros.
    |
    | Valores válidos: propio | tango
    |
    */

    'mode' => env('ERP_MODE', 'propio'),

];

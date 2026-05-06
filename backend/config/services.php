<?php
use App\Core\Container;
use BACKEND\repositories\bodegarepository;
use BACKEND\services\bodegaservice;

// Registro del Repository
Container::set('bodegarepository', function() {
    return new bodegarepository(Container::get('db'));
});

// Registro del Service (inyecta el Repository)
Container::set('bodegaservice', function() {
    $repo = Container::get('bodegarepository');
    return new bodegaservice($repo);
});
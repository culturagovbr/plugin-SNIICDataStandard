<?php

use MapasCulturais\i;

$taxonomies = include APPLICATION_PATH . '/conf/taxonomies.php';

$terms = &$taxonomies[2]['restricted_terms'];
$pos = array_search(i::__('Espetáculo de Circo'), $terms);
if ($pos !== false) {
    array_splice($terms, $pos, 0, [i::__('Espaços e Equipamentos Culturais')]);
} else {
    $terms[] = i::__('Espaços e Equipamentos Culturais');
}

return $taxonomies;

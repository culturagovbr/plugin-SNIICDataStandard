<?php

use MapasCulturais\i;

$taxonomies = include APPLICATION_PATH . '/conf/taxonomies.php';

$taxonomies[2]['restricted_terms'][] = i::__('Espaços e Equipamentos Culturais');
sort($taxonomies[2]['restricted_terms']);

return $taxonomies;

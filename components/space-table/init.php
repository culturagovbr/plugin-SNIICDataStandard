<?php

use MapasCulturais\App;
use MapasCulturais\i;

$app = App::i();

$queryParams = [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$definitions = MapasCulturais\Entities\Space::getPropertiesMetadata();
$skipFields = ['subsite', 'eventOccurrences', 'parent'. 'owner'];
$additionalHeaders = [];
$visibleColumns = [];
foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_")) {
        $data = [
            'text' => $def['label'],
            'value' => $field,
            'slug' => $field
        ];

        if ($field == "owner") {
            $data['text'] = i::__('Responsável', 'space-table');
            $data['value'] = 'owner?.name';
        }

        if ($field == "location") {
            $data['text'] = i::__('Localização', 'space-table');
        }

        if ($field == "public") {
            $data['text'] = i::__('Endereço público', 'space-table');
        }

        $additionalHeaders[] = $data;
        $visibleColumns[] = $field;
    }
}

// Garante que informarQualOutroTipoDeEspaco está presente em visibleColumns e
// additionalHeaders, posicionado logo após 'type', para que o @select dinâmico
// do space-table carregue o campo necessário ao getEntityTypeName.
if (!in_array('informarQualOutroTipoDeEspaco', $visibleColumns)) {
    $typeIndex = array_search('type', $visibleColumns);
    $insertAt = $typeIndex !== false ? $typeIndex + 1 : count($visibleColumns);

    array_splice($visibleColumns, $insertAt, 0, ['informarQualOutroTipoDeEspaco']);
    array_splice($additionalHeaders, $insertAt, 0, [[
        'text'  => i::__('Especificar o tipo de espaço', 'space-table'),
        'value' => 'informarQualOutroTipoDeEspaco',
        'slug'  => 'informarQualOutroTipoDeEspaco',
    ]]);
}

$app->applyHook('component(space-table).additionalHeaders', [$visibleColumns, &$additionalHeaders]);

$this->jsObject['config']['spaceTable'] = [
    'additionalHeaders' => $additionalHeaders,
    'seals'             => $querySeals->getFindResult(),
    'visibleColumns'    => $visibleColumns,
];

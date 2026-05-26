<?php

$space_types = include APPLICATION_PATH . '/conf/space-types.php';

$space_types['items'] = [
    \MapasCulturais\i::__('Tipos de Espaços e Equipamentos Culturais') => [
        'range' => [2000, 2040],
        'items' => [
            2000 => ['name' => \MapasCulturais\i::__('Arena ou semi arena de apresentações')],
            2001 => ['name' => \MapasCulturais\i::__('Associação Comunitária')],
            2002 => ['name' => \MapasCulturais\i::__('Atelier')],
            2003 => ['name' => \MapasCulturais\i::__('Auditório')],
            2004 => ['name' => \MapasCulturais\i::__('Biblioteca')],
            2005 => ['name' => \MapasCulturais\i::__('Biblioteca Comunitária')],
            2006 => ['name' => \MapasCulturais\i::__('Biblioteca Parque')],
            2007 => ['name' => \MapasCulturais\i::__('Casa da Cultura')],
            2008 => ['name' => \MapasCulturais\i::__('Casa de Espetáculo')],
            2009 => ['name' => \MapasCulturais\i::__('Centro Cultural')],
            2010 => ['name' => \MapasCulturais\i::__('Centro de Convenções')],
            2011 => ['name' => \MapasCulturais\i::__('Centro de convivência')],
            2012 => ['name' => \MapasCulturais\i::__('Centro de Memória e Patrimônio')],
            2013 => ['name' => \MapasCulturais\i::__('Centro de Tradição Regional')],
            2014 => ['name' => \MapasCulturais\i::__('Cinemas, cineclubes e salas de exibição')],
            2015 => ['name' => \MapasCulturais\i::__('Cinemateca')],
            2016 => ['name' => \MapasCulturais\i::__('Circo (inclusive itinerante)')],
            2017 => ['name' => \MapasCulturais\i::__('Escola de arte e cultura')],
            2018 => ['name' => \MapasCulturais\i::__('Escola de samba')],
            2019 => ['name' => \MapasCulturais\i::__('Escola de alimentação e cultura')],
            2020 => ['name' => \MapasCulturais\i::__('Espaço de Leitura')],
            2021 => ['name' => \MapasCulturais\i::__('Espaço Multiuso')],
            2022 => ['name' => \MapasCulturais\i::__('Espaços makers')],
            2023 => ['name' => \MapasCulturais\i::__('Estúdio de audiovisual')],
            2024 => ['name' => \MapasCulturais\i::__('Estúdio de Dança')],
            2025 => ['name' => \MapasCulturais\i::__('Estúdio de Música')],
            2026 => ['name' => \MapasCulturais\i::__('FabLabs')],
            2027 => ['name' => \MapasCulturais\i::__('Galeria e espaços de exposição')],
            2028 => ['name' => \MapasCulturais\i::__('Imóvel patrimonializado')],
            2029 => ['name' => \MapasCulturais\i::__('Laboratórios de Economia Criativa')],
            2030 => ['name' => \MapasCulturais\i::__('Livraria, alfarrábio ou sebo')],
            2031 => ['name' => \MapasCulturais\i::__('Memorial')],
            2032 => ['name' => \MapasCulturais\i::__('Mercados de arte e artesanato')],
            2033 => ['name' => \MapasCulturais\i::__('Museu')],
            2034 => ['name' => \MapasCulturais\i::__('Ponto de Leitura')],
            2035 => ['name' => \MapasCulturais\i::__('Pontos e Pontões de Cultura')],
            2036 => ['name' => \MapasCulturais\i::__('Rádios comunitárias')],
            2037 => ['name' => \MapasCulturais\i::__('Sala de Concerto')],
            2038 => ['name' => \MapasCulturais\i::__('Sambódromo')],
            2039 => ['name' => \MapasCulturais\i::__('Teatro')],
            2040 => ['name' => \MapasCulturais\i::__('Outros (informar qual)')],
        ],
    ],
];

$space_types['metadata']['informarQualOutroTipoDeEspaco'] = [
    'label'       => \MapasCulturais\i::__('Especificar o tipo de espaço'),
    'type'        => 'string',
    'validations' => [],
    'should_validate' => function ($entity, $value) {
        $type_id = is_object($entity->type) && isset($entity->type->id)
            ? $entity->type->id
            : (int) ($entity->type ?? 0);

        if ($type_id === 2040 && (empty($value) || trim((string) $value) === '')) {
            return \MapasCulturais\i::__('O campo especificar o tipo de espaço é obrigatório.');
        }

        return false;
    },
    'available_for_opportunities' => true,
];

return $space_types;

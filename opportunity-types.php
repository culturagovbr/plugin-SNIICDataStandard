<?php

$opportunity_types = include APPLICATION_PATH . '/conf/opportunity-types.php';

$opportunity_types['metadata']['segmento'] = [
    'label'   => \MapasCulturais\i::__('Segmento artistico-cultural'),
    'type'    => 'multiselect',
    'options' => [
        \MapasCulturais\i::__('Acervos'),
        \MapasCulturais\i::__('Arquivos'),
        \MapasCulturais\i::__('Artes Visuais'),
        \MapasCulturais\i::__('Artesanato'),
        \MapasCulturais\i::__('Audiovisual'),
        \MapasCulturais\i::__('Capoeira'),
        \MapasCulturais\i::__('Circo'),
        \MapasCulturais\i::__('Cultura de Matriz Africana'),
        \MapasCulturais\i::__('Cultura dos Povos Originários'),
        \MapasCulturais\i::__('Culturas Tradicionais e Populares'),
        \MapasCulturais\i::__('Dança'),
        \MapasCulturais\i::__('Design'),
        \MapasCulturais\i::__('Edição e produção editorial'),
        \MapasCulturais\i::__('Festas e Celebrações'),
        \MapasCulturais\i::__('Hip Hop'),
        \MapasCulturais\i::__('Jogos eletrônicos'),
        \MapasCulturais\i::__('Literatura'),
        \MapasCulturais\i::__('Mediação e formação de leitores'),
        \MapasCulturais\i::__('Moda'),
        \MapasCulturais\i::__('Museu'),
        \MapasCulturais\i::__('Música'),
        \MapasCulturais\i::__('Patrimônio Arqueológico'),
        \MapasCulturais\i::__('Patrimônio Cultural Material'),
        \MapasCulturais\i::__('Patrimônio Cultural Imaterial'),
        \MapasCulturais\i::__('Patrimônio Natural'),
        \MapasCulturais\i::__('Performance'),
        \MapasCulturais\i::__('Teatro'),
        \MapasCulturais\i::__('Outros'),
    ],
];

$opportunity_types['metadata']['etapa'] = [
    'label'   => \MapasCulturais\i::__('Etapa do fazer cultural'),
    'type'    => 'multiselect',
    'options' => [
        \MapasCulturais\i::__('Criação'),
        \MapasCulturais\i::__('Produção'),
        \MapasCulturais\i::__('Comercialização e Distribuição'),
        \MapasCulturais\i::__('Difusão e Circulação'),
        \MapasCulturais\i::__('Acesso, mediação e fruição'),
        \MapasCulturais\i::__('Formação'),
        \MapasCulturais\i::__('Pesquisa e reflexão'),
        \MapasCulturais\i::__('Memória e Preservação'),
        \MapasCulturais\i::__('Organização e gestão'),
        \MapasCulturais\i::__('Monitoramento e avaliação'),
        \MapasCulturais\i::__('Outra (especificar)'),
    ],
];

return $opportunity_types;

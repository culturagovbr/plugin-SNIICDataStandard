<?php

namespace SNIICDataStandard;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Definitions\Metadata;
use MapasCulturais\Entities\Opportunity;

class Plugin extends \MapasCulturais\Plugin
{
    public function _init(): void
    {
        $app = App::i();

        $app->hook('mapas.printJsObject:before', function () {
            $this->jsObject['config']['entityTypes']['space']['otherTypeId'] = 2040;
            $this->jsObject['config']['entityTypes']['space']['otherTypeName'] = 'Outros (informar qual)';
        });

        $app->hook('GET(<<*>>.<<*>>):before', function () use ($app) {
            $app->view->enqueueScript('app', 'sniic-utils', 'js/sniic-utils.js');
        });

        if (isset($app->modules['OpportunityWorkplan'])) {
            $this->_registerOpportunityWorkplanMetadata($app);

            $workplanData = null;

            $app->hook('POST(workplan.save):before', function () use (&$workplanData) {
                $workplanData = json_decode(file_get_contents('php://input'), true) ?? [];
            });

            $app->hook('POST(workplan.save):after', function () use ($app, &$workplanData) {
                if (!$workplanData || empty($workplanData['id'])) {
                    return;
                }

                $workplan = $app->repo('OpportunityWorkplan\Entities\Workplan')->find($workplanData['id']);
                if (!$workplan) {
                    return;
                }

                $app->em->refresh($workplan);

                $requestGoals = $workplanData['goals'] ?? [];
                $existingGoalIds = array_values(array_filter(array_column($requestGoals, 'id')));

                $workplanGoals = $workplan->goals->toArray();

                $newGoalsInWorkplan = array_values(array_filter(
                    $workplanGoals,
                    fn($g) => !in_array($g->id, $existingGoalIds)
                ));

                $newGoalIdx = 0;

                foreach ($requestGoals as $g) {
                    if (!empty($g['id'])) {
                        $goal = $app->repo('OpportunityWorkplan\Entities\Goal')->find($g['id']);
                    } else {
                        $goal = $newGoalsInWorkplan[$newGoalIdx] ?? null;
                        $newGoalIdx++;
                    }

                    if (!$goal) {
                        continue;
                    }

                    $goal->culturalMakingStageOther = $g['culturalMakingStageOther'] ?? null;
                    $goal->save(true);

                    $requestDeliveries = $g['deliveries'] ?? [];
                    $app->em->refresh($goal);
                    $savedDeliveries = $goal->deliveries->toArray();
                    $existingDeliveryIds = array_values(array_filter(array_column($requestDeliveries, 'id')));

                    $newDeliveriesInGoal = array_values(array_filter(
                        $savedDeliveries,
                        fn($d) => !in_array($d->id, $existingDeliveryIds)
                    ));

                    $newDeliveryIdx = 0;

                    foreach ($requestDeliveries as $d) {
                        if (!empty($d['id'])) {
                            $delivery = $app->repo('OpportunityWorkplan\Entities\Delivery')->find($d['id']);
                        } else {
                            $delivery = $newDeliveriesInGoal[$newDeliveryIdx] ?? null;
                            $newDeliveryIdx++;
                        }

                        if (!$delivery) {
                            continue;
                        }

                        $delivery->typeDeliveryOther              = $d['typeDeliveryOther'] ?? null;
                        $delivery->artChainLink                   = $d['artChainLink'] ?? null;
                        $delivery->totalBudget                    = $d['totalBudget'] ?? null;
                        $delivery->numberOfCities                 = $d['numberOfCities'] ?? null;
                        $delivery->numberOfNeighborhoods          = $d['numberOfNeighborhoods'] ?? null;
                        $delivery->mediationActions               = $d['mediationActions'] ?? null;
                        $delivery->paidStaffByRole                = $d['paidStaffByRole'] ?? null;
                        $delivery->teamCompositionGender          = $d['teamCompositionGender'] ?? null;
                        $delivery->teamCompositionRace            = $d['teamCompositionRace'] ?? null;
                        $delivery->revenueType                    = $d['revenueType'] ?? null;
                        $delivery->commercialUnits                = $d['commercialUnits'] ?? null;
                        $delivery->unitPrice                      = $d['unitPrice'] ?? null;
                        $delivery->monthInitial                   = $d['monthInitial'] ?? null;
                        $delivery->monthEnd                       = $d['monthEnd'] ?? null;
                        $delivery->hasCommunityCoauthors          = $d['hasCommunityCoauthors'] ?? null;
                        $delivery->communityCoauthorsDetail       = $d['communityCoauthorsDetail'] ?? null;
                        $delivery->hasTransInclusionStrategy      = $d['hasTransInclusionStrategy'] ?? null;
                        $delivery->transInclusionActions          = $d['transInclusionActions'] ?? null;
                        $delivery->hasAccessibilityPlan           = $d['hasAccessibilityPlan'] ?? null;
                        $delivery->expectedAccessibilityMeasures  = $d['expectedAccessibilityMeasures'] ?? null;
                        $delivery->hasEnvironmentalPractices      = $d['hasEnvironmentalPractices'] ?? null;
                        $delivery->environmentalPracticesDescription = $d['environmentalPracticesDescription'] ?? null;
                        $delivery->hasPressStrategy               = $d['hasPressStrategy'] ?? null;
                        $delivery->communicationChannels          = $d['communicationChannels'] ?? null;
                        $delivery->hasInnovationAction            = $d['hasInnovationAction'] ?? null;
                        $delivery->innovationTypes                = $d['innovationTypes'] ?? null;
                        $delivery->documentationTypes             = $d['documentationTypes'] ?? null;
                        $delivery->save(true);
                    }
                }

                $app->em->refresh($workplan);
                foreach ($workplan->goals as $goal) {
                    $app->em->refresh($goal);
                }
            });
        }

        if (isset($app->modules['ProjectMonitoring'])) {
            $this->_registerProjectMonitoringMetadata($app);

            $monitoringData = null;

            $app->hook('POST(monitoring.save):before', function () use (&$monitoringData) {
                $monitoringData = json_decode(file_get_contents('php://input'), true) ?? [];
            });

            $app->hook('POST(monitoring.save):after', function () use ($app, &$monitoringData) {
                if (!$monitoringData) {
                    return;
                }

                foreach ($monitoringData['deliveries'] ?? [] as $d) {
                    if (empty($d['id'])) {
                        continue;
                    }

                    $delivery = $app->repo('OpportunityWorkplan\Entities\Delivery')->find($d['id']);
                    if (!$delivery) {
                        continue;
                    }

                    $delivery->executedNumberOfCities               = $d['executedNumberOfCities'] ?? null;
                    $delivery->executedNumberOfNeighborhoods        = $d['executedNumberOfNeighborhoods'] ?? null;
                    $delivery->executedMediationActions             = $d['executedMediationActions'] ?? null;
                    $delivery->executedCommercialUnits              = $d['executedCommercialUnits'] ?? null;
                    $delivery->executedUnitPrice                    = $d['executedUnitPrice'] ?? null;
                    $delivery->executedPaidStaffByRole              = $d['executedPaidStaffByRole'] ?? null;
                    $delivery->executedTeamCompositionGender        = $d['executedTeamCompositionGender'] ?? null;
                    $delivery->executedTeamCompositionRace          = $d['executedTeamCompositionRace'] ?? null;
                    $delivery->executedArtChainLink                 = $d['executedArtChainLink'] ?? null;
                    $delivery->executedCommunicationChannels        = $d['executedCommunicationChannels'] ?? null;
                    $delivery->executedRevenueType                  = $d['executedRevenueType'] ?? null;
                    $delivery->executedSegmentDelivery              = $d['executedSegmentDelivery'] ?? null;
                    $delivery->executedHasCommunityCoauthors        = $d['executedHasCommunityCoauthors'] ?? null;
                    $delivery->executedCommunityCoauthorsDetail     = $d['executedCommunityCoauthorsDetail'] ?? null;
                    $delivery->executedHasTransInclusionStrategy    = $d['executedHasTransInclusionStrategy'] ?? null;
                    $delivery->executedTransInclusionActions        = $d['executedTransInclusionActions'] ?? null;
                    $delivery->executedHasAccessibilityPlan         = $d['executedHasAccessibilityPlan'] ?? null;
                    $delivery->executedExpectedAccessibilityMeasures = $d['executedExpectedAccessibilityMeasures'] ?? null;
                    $delivery->executedHasEnvironmentalPractices    = $d['executedHasEnvironmentalPractices'] ?? null;
                    $delivery->executedEnvironmentalPracticesDescription = $d['executedEnvironmentalPracticesDescription'] ?? null;
                    $delivery->executedHasPressStrategy             = $d['executedHasPressStrategy'] ?? null;
                    $delivery->executedHasInnovationAction          = $d['executedHasInnovationAction'] ?? null;
                    $delivery->executedInnovationTypes              = $d['executedInnovationTypes'] ?? null;
                    $delivery->executedDocumentationTypes           = $d['executedDocumentationTypes'] ?? null;
                    $delivery->save(true);
                }
            });
        }
    }

    public function register(): void {}

    private function _registerOpportunityWorkplanMetadata(App $app): void
    {
        $Workplan = 'OpportunityWorkplan\Entities\Workplan';
        $Goal     = 'OpportunityWorkplan\Entities\Goal';
        $Delivery = 'OpportunityWorkplan\Entities\Delivery';

        // ── Workplan ──────────────────────────────────────────────────────────
        $app->registerMetadata(new Metadata('thematicAgenda', [
            'label' => i::__('Pauta temática'),
            'type'  => 'select',
            'options' => [
                i::__('Não se relaciona a nenhuma pauta temática'),
                i::__('Cultura Alimentar'),
                i::__('Cultura DEF'),
                i::__('Cultura Digital'),
                i::__('Culturas Imigrantes e Refugiadas'),
                i::__('Cultura LGBTQIAPN+'),
                i::__('Cultura, Memória e Direitos Humanos'),
                i::__('Cultura Nerd'),
                i::__('Culturas Periféricas'),
                i::__('Cultura Quilombola'),
                i::__('Culturas Rurais e Agroecológicas'),
                i::__('Culturas Urbanas'),
                i::__('Cultura do Sertão'),
                i::__('Cultura e Acessibilidade'),
                i::__('Cultura e Economia Criativa'),
                i::__('Cultura e Educação'),
                i::__('Cultura e Gênero'),
                i::__('Cultura e Idosos'),
                i::__('Cultura e Infância'),
                i::__('Cultura e Juventude'),
                i::__('Cultura e Meio ambiente'),
                i::__('Cultura e Negritude'),
                i::__('Cultura e Pessoas em Situação de Privação de Liberdade'),
                i::__('Cultura e População de Rua'),
                i::__('Cultura e Povos Ciganos'),
                i::__('Cultura e Saúde'),
                i::__('Cultura e Turismo'),
                i::__('Culturas Indígenas'),
                i::__('Culturas Tradicionais de Matriz Africana'),
                i::__('Outra (especificar)'),
            ],
        ]), $Workplan);

        // ── Goal ──────────────────────────────────────────────────────────────
        $app->registerMetadata(new Metadata('culturalMakingStageOther', [
            'label' => i::__('Especificar etapa do fazer cultural'),
            'type'  => 'string',
        ]), $Goal);

        // ── Delivery ─────────────────────────────────────────────────────────
        $app->registerMetadata(new Metadata('typeDeliveryOther', [
            'label' => i::__('Especificar tipo de entrega'),
            'type'  => 'string',
        ]), $Delivery);

        $app->registerMetadata(new Metadata('artChainLink', [
            'label' => i::__('Principal elo das artes acionado'),
            'type'  => 'select',
            'options' => [
                i::__('Acesso'),
                i::__('Criação'),
                i::__('Produção'),
                i::__('Difusão'),
                i::__('Circulação'),
                i::__('Internacionalização'),
                i::__('Formação'),
                i::__('Fruição'),
                i::__('Memória/Preservação'),
                i::__('Pesquisa'),
                i::__('Reflexão'),
                i::__('Gestão Cultural'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('totalBudget', [
            'label' => i::__('Qual o orçamento total da atividade?'),
            'type'  => 'currency',
        ]), $Delivery);

        $app->registerMetadata(new Metadata('numberOfCities', [
            'label' => i::__('Em quantos municípios a atividade vai ser realizada?'),
            'type'  => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('numberOfNeighborhoods', [
            'label' => i::__('Em quantos bairros a atividade vai ser realizada?'),
            'type'  => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('mediationActions', [
            'label' => i::__('Quantas ações de mediação/formação de público estão previstas?'),
            'type'  => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('paidStaffByRole', [
            'label' => i::__('Quantas pessoas serão remuneradas, por função?'),
            'type'  => 'json',
            'options' => [
                i::__('Diretor Artístico'), i::__('Diretor de Arte'), i::__('Diretor Musical'),
                i::__('Produtor Cultural'), i::__('Produtor Musical'), i::__('Produtor Audiovisual'),
                i::__('Gestor Cultural'), i::__('Curador'), i::__('Assistente de produção'),
                i::__('Assistente de direção'), i::__('Ator/Atriz'), i::__('Bailarino'),
                i::__('Dançarino'), i::__('Coreógrafo'), i::__('Dramaturgo'), i::__('Iluminador'),
                i::__('Cenotécnico'), i::__('Figurinista'), i::__('Maquiador'), i::__('Contra-regra'),
                i::__('Assistente de palco'), i::__('Músico/Musicista'), i::__('Cantor'),
                i::__('Compositor'), i::__('Arranjador'), i::__('Maestro'), i::__('Instrumentista'),
                i::__('DJ'), i::__('Artista Visual'), i::__('Pintor'), i::__('Escultor'),
                i::__('Fotógrafo'), i::__('Designer Gráfico'), i::__('Ilustrador'),
                i::__('Grafiteiro'), i::__('Muralista'), i::__('Roteirista'),
                i::__('Operador de Câmera'), i::__('Editor de Vídeo'), i::__('Operador de Som'),
                i::__('Técnico de Iluminação'), i::__('Finalizador'), i::__('Escritor'),
                i::__('Poeta'), i::__('Contador de Histórias'), i::__('Jornalista'),
                i::__('Redator'), i::__('Editor de Livros'), i::__('Revisor'), i::__('Tradutor'),
                i::__('Educador Cultural'), i::__('Mediador'), i::__('Oficineiro'),
                i::__('Professor'), i::__('Instrutor'), i::__('Técnico de Som'),
                i::__('Montador de Palco'), i::__('Maquinista'), i::__('Eletricista'),
                i::__('Engenheiro de Som'), i::__('Mestre de Cultura Popular'), i::__('Brincante'),
                i::__('Artesão'), i::__('Capoeirista'), i::__('Desenvolvedor de Software'),
                i::__('Web Designer'), i::__('Designer de Som'), i::__('Gestor de Redes Sociais'),
                i::__('Coordenador'), i::__('Secretário'), i::__('Assistente Administrativo'),
                i::__('Contador'), i::__('Pesquisador'), i::__('Consultor Cultural'),
                i::__('Assessor de Imprensa'), i::__('Outra'),
            ],
            'serialize'   => fn($val) => json_encode($val),
            'unserialize' => fn($val) => json_decode((string) $val, true),
        ]), $Delivery);

        $app->registerMetadata(new Metadata('teamCompositionGender', [
            'label'       => i::__('Composição prevista da equipe por gênero'),
            'type'        => 'json',
            'serialize'   => fn($val) => json_encode($val),
            'unserialize' => fn($val) => json_decode((string) $val, true),
        ]), $Delivery);

        $app->registerMetadata(new Metadata('teamCompositionRace', [
            'label'       => i::__('Composição prevista da equipe por raça/cor'),
            'type'        => 'json',
            'serialize'   => fn($val) => json_encode($val),
            'unserialize' => fn($val) => json_decode((string) $val, true),
        ]), $Delivery);

        $app->registerMetadata(new Metadata('revenueType', [
            'label' => i::__('Qual o tipo de receita previsto?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Venda de ingressos'),
                i::__('Venda de produtos'),
                i::__('Patrocínio privado'),
                i::__('Apoio cultural'),
                i::__('Doações'),
                i::__('Cachê'),
                i::__('Prestação de serviços'),
                i::__('Direitos autorais'),
                i::__('Licenciamento'),
                i::__('Não haverá receita'),
                i::__('Outros'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('commercialUnits', [
            'label' => i::__('Quantidade de unidades previstas para comercialização'),
            'type'  => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('unitPrice', [
            'label' => i::__('Valor unitário previsto (R$)'),
            'type'  => 'currency',
        ]), $Delivery);

        $app->registerMetadata(new Metadata('hasCommunityCoauthors', [
            'label' => i::__('A atividade prevê envolvimento de comunidades/coletivos como coautores/coexecutores?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('communityCoauthorsDetail', [
            'label' => i::__('Detalhamento de coautoria'),
            'type'  => 'text',
        ]), $Delivery);

        $app->registerMetadata(new Metadata('hasTransInclusionStrategy', [
            'label' => i::__('A atividade prevê estratégias voltadas à promoção do acesso de pessoas Trans e Travestis?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('transInclusionActions', [
            'label' => i::__('Quais ações foram previstas para incorporar estratégias voltadas à promoção do acesso de pessoas Trans e Travestis?'),
            'type'  => 'text',
        ]), $Delivery);

        $app->registerMetadata(new Metadata('hasAccessibilityPlan', [
            'label' => i::__('A atividade prevê medidas de acessibilidade?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('expectedAccessibilityMeasures', [
            'label' => i::__('Quais medidas de acessibilidade estão previstas na atividade?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Rotas acessíveis, com espaço de manobra para cadeira de rodas'),
                i::__('Palco acessível'),
                i::__('Camarim acessível'),
                i::__('Piso tátil'),
                i::__('Rampas'),
                i::__("Elevadores adequados para PCD's"),
                i::__('Corrimãos e guarda-corpos'),
                i::__("Banheiros adaptados para PCD's"),
                i::__('Área de alimentação preferencial identificada'),
                i::__("Vagas de estacionamento para PCD's reservadas"),
                i::__("Assentos para pessoas obesas, pessoas com mobilidade reduzida, PCD's e pessoas idosas reservadas"),
                i::__('Filas preferenciais identificadas'),
                i::__('Iluminação adequada'),
                i::__('Livro e/ou similares em braile'),
                i::__('Audiolivro'),
                i::__('Uso Língua Brasileira de Sinais - Libras'),
                i::__('Sistema Braille em materiais impressos'),
                i::__('Sistema de sinalização ou comunicação tátil'),
                i::__('Audiodescrição'),
                i::__('Legendas para surdos e ensurdecidos'),
                i::__('Linguagem simples'),
                i::__('Textos adaptados para software de leitor de tela'),
                i::__('Capacitação em acessibilidade para equipes atuantes nos projetos culturais'),
                i::__('Contratação de profissionais especializados em acessibilidade cultural'),
                i::__('Contratação de profissionais com deficiência'),
                i::__('Formação e sensibilização de agentes culturais sobre acessibilidade'),
                i::__('Formação e sensibilização de públicos da cadeia produtiva cultural sobre acessibilidade'),
                i::__("Envolvimento de PCD's na concepção do projeto"),
                i::__('Outras'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('hasEnvironmentalPractices', [
            'label' => i::__('A atividade prevê medidas ou práticas socioambientais?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('environmentalPracticesDescription', [
            'label' => i::__('Quais medidas e práticas socioambientais estão previstas na atividade?'),
            'type'  => 'text',
        ]), $Delivery);

        $app->registerMetadata(new Metadata('hasPressStrategy', [
            'label' => i::__('A atividade contará com uma estratégia de relacionamento com a imprensa?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('communicationChannels', [
            'label' => i::__('Quais canais de comunicação estão previstos para difusão da atividade?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Instagram'),
                i::__('Facebook'),
                i::__('TikTok'),
                i::__('YouTube'),
                i::__('X/Twitter'),
                i::__('WhatsApp (listas/grupos)'),
                i::__('Telegram (canais/grupos)'),
                i::__('Site/página oficial do projeto'),
                i::__('E-mail marketing/newsletter'),
                i::__('Plataformas de eventos/inscrição (ex.: Sympla/Shotgun/Eventbrite)'),
                i::__('Portais, blogs e influenciadores/as locais'),
                i::__('Rádio comunitária'),
                i::__('Rádio comercial'),
                i::__('TV local'),
                i::__('Mídia impressa'),
                i::__('Cartazes e materiais impressos'),
                i::__('Carro de som'),
                i::__('Outros'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('hasInnovationAction', [
            'label' => i::__('A atividade prevê ao menos uma ação de experimentação/inovação?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('innovationTypes', [
            'label' => i::__('Quais tipos de experimentação/inovação previstos na atividade?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Uso de novas tecnologias (AR, VR, IA, etc.)'),
                i::__('Novas linguagens artísticas'),
                i::__('Fusão de linguagens'),
                i::__('Metodologias participativas inovadoras'),
                i::__('Novos modelos de gestão cultural'),
                i::__('Economia criativa e novos modelos de negócio'),
                i::__('Sustentabilidade e práticas ambientais inovadoras'),
                i::__('Inclusão e acessibilidade de forma inovadora'),
                i::__('Experimentação em espaços não convencionais'),
                i::__('Coprodução/cocriação com públicos'),
                i::__('Outros'),
            ],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('documentationTypes', [
            'label' => i::__('Tipo de documentação que será produzida'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Fotografia'),
                i::__('Vídeo'),
                i::__('Áudio'),
                i::__('Relatório textual'),
                i::__('Caderno de processo'),
                i::__('Publicação impressa'),
                i::__('Publicação digital'),
                i::__('Website/Plataforma online'),
                i::__('Redes sociais'),
                i::__('Depoimentos'),
                i::__('Registros de processo'),
                i::__('Acervo digitalizado'),
                i::__('Não haverá documentação específica'),
                i::__('Outros'),
            ],
        ]), $Delivery);

        // ── Opportunity metadata (flags de configuração do workplan) ──────────
        $opportunityFlags = [
            'workplan_deliveryInformArtChainLink'       => i::__('Informar principal elo das artes acionado'),
            'workplan_deliveryRequireArtChainLink'      => i::__('Principal elo das artes é obrigatório'),
            'workplan_deliveryInformTotalBudget'        => i::__('Informar orçamento total da atividade'),
            'workplan_deliveryRequireTotalBudget'       => i::__('Orçamento total é obrigatório'),
            'workplan_deliveryInformNumberOfCities'     => i::__('Informar número de municípios'),
            'workplan_deliveryRequireNumberOfCities'    => i::__('Número de municípios é obrigatório'),
            'workplan_deliveryInformNumberOfNeighborhoods'  => i::__('Informar número de bairros'),
            'workplan_deliveryRequireNumberOfNeighborhoods' => i::__('Número de bairros é obrigatório'),
            'workplan_deliveryInformMediationActions'   => i::__('Informar número de ações de mediação/formação de público'),
            'workplan_deliveryRequireMediationActions'  => i::__('Número de ações de mediação é obrigatório'),
            'workplan_deliveryInformPaidStaffByRole'    => i::__('Informar pessoas remuneradas por função'),
            'workplan_deliveryRequirePaidStaffByRole'   => i::__('Pessoas remuneradas por função é obrigatório'),
            'workplan_deliveryInformTeamComposition'    => i::__('Informar composição da equipe (gênero e raça/cor)'),
            'workplan_deliveryRequireTeamCompositionGender' => i::__('Composição da equipe por gênero é obrigatória'),
            'workplan_deliveryRequireTeamCompositionRace'   => i::__('Composição da equipe por raça/cor é obrigatória'),
            'workplan_deliveryInformRevenueType'        => i::__('Informar tipo de receita previsto'),
            'workplan_deliveryRequireRevenueType'       => i::__('Tipo de receita previsto é obrigatório'),
            'workplan_deliveryInformCommercialUnits'    => i::__('Informar unidades para comercialização'),
            'workplan_deliveryRequireCommercialUnits'   => i::__('Unidades para comercialização é obrigatório'),
            'workplan_deliveryRequireUnitPrice'         => i::__('Valor unitário previsto é obrigatório'),
            'workplan_deliveryInformCommunityCoauthors' => i::__('Informar envolvimento de comunidades como coautores'),
            'workplan_deliveryRequireCommunityCoauthorsDetail' => i::__('Detalhamento de coautoria com comunidades é obrigatório'),
            'workplan_deliveryInformTransInclusion'     => i::__('Informar estratégias de inclusão Trans e Travestis'),
            'workplan_deliveryRequireTransInclusionActions' => i::__('Ações de inclusão Trans/Travestis são obrigatórias'),
            'workplan_deliveryInformAccessibilityPlan'  => i::__('Informar medidas de acessibilidade previstas'),
            'workplan_deliveryRequireExpectedAccessibilityMeasures' => i::__('Medidas de acessibilidade previstas são obrigatórias'),
            'workplan_deliveryInformEnvironmentalPractices' => i::__('Informar práticas socioambientais'),
            'workplan_deliveryRequireEnvironmentalPracticesDescription' => i::__('Descrição de práticas socioambientais é obrigatória'),
            'workplan_deliveryInformPressStrategy'      => i::__('Informar estratégia de relacionamento com imprensa'),
            'workplan_deliveryRequireHasPressStrategy'  => i::__('Estratégias de comunicação são obrigatórias'),
            'workplan_deliveryInformCommunicationChannels' => i::__('Informar canais de comunicação'),
            'workplan_deliveryRequireCommunicationChannels' => i::__('Canais de comunicação são obrigatórios'),
            'workplan_deliveryInformInnovation'         => i::__('Informar ações de experimentação/inovação'),
            'workplan_deliveryRequireInnovationTypes'   => i::__('Tipos de experimentação/inovação são obrigatórios'),
            'workplan_deliveryInformDocumentationTypes' => i::__('Informar tipo de documentação'),
            'workplan_deliveryRequireDocumentationTypes' => i::__('Tipos de documentação são obrigatórios'),
            'workplan_deliveryRequireSegment'           => i::__('Segmento artístico-cultural (entrega) é obrigatório'),
            'workplan_deliveryRequireExpectedNumberPeople' => i::__('Quantidade estimada de público é obrigatória'),
            'workplan_deliveryInformDeliveryPeriod'     => i::__('Informar período de realização da atividade'),
            'workplan_deliveryRequireDeliveryPeriod'    => i::__('Período de realização obrigatório'),
            'workplan_dataProjectInformCulturalArtisticSegment' => i::__('Informar segmento artístico-cultural'),
            'workplan_goalInformTitle'                  => i::__('Informar título da meta'),
            'workplan_goalRequireTitle'                 => i::__('Título da meta é obrigatório'),
            'workplan_goalInformDescription'            => i::__('Informar descrição da meta'),
            'workplan_goalRequireDescription'           => i::__('Descrição da meta é obrigatória'),
            'workplan_metaInformTheStageOfCulturalMaking' => i::__('Informar etapa do fazer cultural'),
            'workplan_monitoringInformNumberOfParticipants' => i::__('Informar número de participantes executado'),
            'workplan_monitoringInformNumberOfCities'   => i::__('Informar número de municípios executados'),
            'workplan_monitoringRequireNumberOfCities'  => i::__('Número de municípios executados é obrigatório'),
            'workplan_monitoringInformNumberOfNeighborhoods'  => i::__('Informar número de bairros executados'),
            'workplan_monitoringRequireNumberOfNeighborhoods' => i::__('Número de bairros executados é obrigatório'),
            'workplan_monitoringInformMediationActions' => i::__('Informar ações de mediação executadas'),
            'workplan_monitoringRequireMediationActions' => i::__('Ações de mediação executadas são obrigatórias'),
            'workplan_monitoringRequireNumberOfParticipants' => i::__('Número de participantes executado é obrigatório'),
            'workplan_monitoringInformCommercialUnits'  => i::__('Informar unidades comercializadas executadas'),
            'workplan_monitoringRequireCommercialUnits' => i::__('Unidades comercializadas executadas são obrigatórias'),
            'workplan_monitoringRequireUnitPrice'       => i::__('Valor unitário executado é obrigatório'),
            'workplan_monitoringInformPaidStaffByRole'  => i::__('Informar pessoas remuneradas executadas por função'),
            'workplan_monitoringRequirePaidStaffByRole' => i::__('Pessoas remuneradas executadas por função é obrigatório'),
            'workplan_monitoringInformTeamComposition'  => i::__('Informar composição da equipe executada (gênero e raça/cor)'),
            'workplan_monitoringRequireTeamCompositionGender' => i::__('Composição da equipe executada por gênero é obrigatória'),
            'workplan_monitoringRequireTeamCompositionRace'   => i::__('Composição da equipe executada por raça/cor é obrigatória'),
            'workplan_monitoringInformRevenueType'      => i::__('Informar tipo de receita executada'),
            'workplan_monitoringRequireRevenueType'     => i::__('Tipo de receita executada é obrigatório'),
            'workplan_monitoringInformCommunityCoauthors' => i::__('Informar envolvimento executado de comunidades/coletivos como coautores/coexecutores'),
            'workplan_monitoringRequireCommunityCoauthorsDetail' => i::__('Detalhamento de coautoria/coexecução executada é obrigatório'),
            'workplan_monitoringInformTransInclusion'   => i::__('Informar estratégias executadas de inclusão Trans e Travestis'),
            'workplan_monitoringRequireTransInclusionActions' => i::__('Ações executadas de inclusão Trans e Travestis são obrigatórias'),
            'workplan_monitoringInformAccessibilityPlan' => i::__('Informar plano de acessibilidade executado'),
            'workplan_monitoringRequireExpectedAccessibilityMeasures' => i::__('Medidas de acessibilidade executadas são obrigatórias'),
            'workplan_monitoringInformEnvironmentalPractices' => i::__('Informar práticas socioambientais executadas'),
            'workplan_monitoringRequireEnvironmentalPracticesDescription' => i::__('Práticas socioambientais executadas são obrigatórias'),
            'workplan_monitoringInformPressStrategy'    => i::__('Informar estratégia executada de relacionamento com imprensa'),
            'workplan_monitoringInformInnovation'       => i::__('Informar ações executadas de experimentação/inovação'),
            'workplan_monitoringRequireInnovationTypes' => i::__('Tipos de experimentação/inovação executados são obrigatórios'),
            'workplan_monitoringInformDocumentationTypes' => i::__('Informar tipos de documentação produzida (executado)'),
            'workplan_monitoringRequireDocumentationTypes' => i::__('Tipos de documentação produzida são obrigatórios'),
            'workplan_monitoringInformSegmentDelivery'  => i::__('Informar segmento artístico-cultural executado da entrega'),
            'workplan_monitoringRequireSegmentDelivery' => i::__('Segmento artístico-cultural executado é obrigatório'),
            'workplan_monitoringInformArtChainLink'     => i::__('Informar principal elo das artes acionado (executado)'),
            'workplan_monitoringRequireArtChainLink'    => i::__('Principal elo das artes (executado) é obrigatório'),
            'workplan_monitoringInformCommunicationChannels' => i::__('Informar canais de comunicação utilizados (executado)'),
            'workplan_monitoringRequireCommunicationChannels' => i::__('Canais de comunicação utilizados são obrigatórios'),
            'workplan_dataProjectRequireCulturalArtisticSegment' => i::__('Segmento artístico-cultural é obrigatório'),
            'workplan_monitoringRequireAvailabilityType'  => i::__('Forma de disponibilização é obrigatória'),
            'workplan_monitoringRequireAccessibilityMeasures' => i::__('Medidas de acessibilidade executadas são obrigatórias'),
            'workplan_monitoringRequireParticipantProfile' => i::__('Perfil do público é obrigatório'),
            'workplan_monitoringRequirePriorityAudience'  => i::__('Territórios prioritários são obrigatórios'),
            'workplan_monitoringRequireExecutedRevenue'   => i::__('Receita executada é obrigatória'),
        ];

        foreach ($opportunityFlags as $key => $label) {
            $app->registerMetadata(new Metadata($key, [
                'label'         => $label,
                'type'          => 'boolean',
                'default_value' => false,
            ]), Opportunity::class);
        }
    }

    private function _registerProjectMonitoringMetadata(App $app): void
    {
        $Delivery = 'OpportunityWorkplan\Entities\Delivery';

        // Override priorityAudience options to match PR#129 order
        $app->registerMetadata(new Metadata('priorityAudience', [
            'label' => i::__('Territórios prioritários'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Não se aplica'),
                i::__('Área atingida por desastre natural'),
                i::__('Assentamento ou acampamento'),
                i::__('Conjunto ou empreendimento habitacional de interesse social'),
                i::__('Favelas e comunidades urbanas'),
                i::__('Periferia'),
                i::__('Regiões com menor histórico de acesso aos recursos da política pública de cultura'),
                i::__('Regiões com menor índice de Desenvolvimento Humano - IDH'),
                i::__('Sítios de arqueológicos e de patrimônio cultural'),
                i::__('Território de fronteira'),
                i::__('Território de povos e comunidades tradicionais'),
                i::__('Território indígena'),
                i::__('Território rural'),
                i::__('Zona especial de interesse social'),
            ],
            'should_validate' => function ($entity) {
                if ($entity->isMetadataRequired('priorityAudience')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            },
        ]), $Delivery);

        // ── Executed delivery fields ──────────────────────────────────────────
        $app->registerMetadata(new Metadata('executedNumberOfCities', [
            'label' => i::__('Em quantos municípios a atividade foi realizada?'),
            'type'  => 'integer',
            'validations' => ['v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedNumberOfCities') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedNumberOfNeighborhoods', [
            'label' => i::__('Em quantos bairros a atividade foi realizada?'),
            'type'  => 'integer',
            'validations' => ['v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedNumberOfNeighborhoods') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedMediationActions', [
            'label' => i::__('Quantas ações de mediação/formação de público foram realizadas?'),
            'type'  => 'integer',
            'validations' => ['v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedMediationActions') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedCommercialUnits', [
            'label' => i::__('Quantidade de unidades efetivamente comercializadas'),
            'type'  => 'integer',
            'validations' => ['v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedCommercialUnits') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedUnitPrice', [
            'label' => i::__('Valor unitário praticado (R$)'),
            'type'  => 'currency',
            'should_validate' => fn($e) => $e->isMetadataRequired('executedUnitPrice') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedPaidStaffByRole', [
            'label'       => i::__('Quantas pessoas foram remuneradas, por função?'),
            'type'        => 'json',
            'serialize'   => fn($val) => json_encode($val),
            'unserialize' => fn($val) => json_decode((string) $val, true),
            'should_validate' => fn($e) => $e->isMetadataRequired('executedPaidStaffByRole') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedTeamCompositionGender', [
            'label'       => i::__('Composição efetiva da equipe por gênero'),
            'type'        => 'json',
            'serialize'   => fn($val) => json_encode($val),
            'unserialize' => fn($val) => json_decode((string) $val, true),
            'should_validate' => fn($e) => $e->isMetadataRequired('executedTeamCompositionGender') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedTeamCompositionRace', [
            'label'       => i::__('Composição efetiva da equipe por raça/cor'),
            'type'        => 'json',
            'serialize'   => fn($val) => json_encode($val),
            'unserialize' => fn($val) => json_decode((string) $val, true),
            'should_validate' => fn($e) => $e->isMetadataRequired('executedTeamCompositionRace') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedArtChainLink', [
            'label' => i::__('Principal elo das artes acionado (executado)'),
            'type'  => 'select',
            'options' => [
                i::__('Acesso'), i::__('Criação'), i::__('Produção'), i::__('Difusão'),
                i::__('Circulação'), i::__('Internacionalização'), i::__('Formação'),
                i::__('Fruição'), i::__('Memória/Preservação'), i::__('Pesquisa'),
                i::__('Reflexão'), i::__('Gestão Cultural'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedArtChainLink') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedCommunicationChannels', [
            'label' => i::__('Canais de comunicação utilizados (executado)'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Instagram'), i::__('Facebook'), i::__('TikTok'), i::__('YouTube'),
                i::__('X/Twitter'), i::__('WhatsApp (listas/grupos)'), i::__('Telegram (canais/grupos)'),
                i::__('Site/página oficial do projeto'), i::__('E-mail marketing/newsletter'),
                i::__('Plataformas de eventos/inscrição (ex.: Sympla/Shotgun/Eventbrite)'),
                i::__('Portais, blogs e influenciadores/as locais'),
                i::__('Rádio comunitária'), i::__('Rádio comercial'), i::__('TV local'),
                i::__('Mídia impressa'), i::__('Cartazes e materiais impressos'),
                i::__('Carro de som'), i::__('Outros'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedCommunicationChannels') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedRevenueType', [
            'label' => i::__('Qual o tipo de receita executada?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Venda de ingressos'), i::__('Venda de produtos'), i::__('Patrocínio privado'),
                i::__('Apoio cultural'), i::__('Doações'), i::__('Cachê'),
                i::__('Prestação de serviços'), i::__('Direitos autorais'), i::__('Licenciamento'),
                i::__('Não haverá receita'), i::__('Outros'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedRevenueType') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedSegmentDelivery', [
            'label' => i::__('Segmento artístico-cultural executado da entrega'),
            'type'  => 'select',
            'options' => [
                i::__('Artes Visuais'), i::__('Artesanato'), i::__('Audiovisual e Mídias Interativas'),
                i::__('Circo'), i::__('Culturas Tradicionais e Populares'),
                i::__('Culturas dos Povos Originários'), i::__('Dança'),
                i::__('Design e Serviços Criativos'),
                i::__('Economia, Produção e Áreas Técnicas da Cultura'),
                i::__('Festas Populares'), i::__('Humanidades'), i::__('Livro, Leitura e Literatura'),
                i::__('Música'), i::__('Patrimônio Cultural Imaterial'),
                i::__('Patrimônio Cultural Material'), i::__('Performance'),
                i::__('Produção e Áreas Técnicas da Cultura'), i::__('Teatro'),
                i::__('Transversalidades'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedSegmentDelivery') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedHasCommunityCoauthors', [
            'label' => i::__('A atividade executada contou com envolvimento de comunidades/coletivos como coautores/coexecutores?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedCommunityCoauthorsDetail', [
            'label' => i::__('Descreva o envolvimento executado das comunidades/coletivos como coautores/coexecutores'),
            'type'  => 'text',
            'should_validate' => fn($e) => $e->isMetadataRequired('executedCommunityCoauthorsDetail') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedHasTransInclusionStrategy', [
            'label' => i::__('A atividade executada contou com estratégias voltadas à promoção do acesso de pessoas Trans e Travestis?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedTransInclusionActions', [
            'label' => i::__('Quais ações executadas promoveram o acesso de pessoas Trans e Travestis?'),
            'type'  => 'text',
            'should_validate' => fn($e) => $e->isMetadataRequired('executedTransInclusionActions') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedHasAccessibilityPlan', [
            'label' => i::__('A atividade executada contou com medidas de acessibilidade?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedExpectedAccessibilityMeasures', [
            'label' => i::__('Quais medidas de acessibilidade foram executadas na atividade?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Rotas acessíveis, com espaço de manobra para cadeira de rodas'),
                i::__('Palco acessível'), i::__('Camarim acessível'), i::__('Piso tátil'),
                i::__('Rampas'), i::__("Elevadores adequados para PCD's"),
                i::__('Corrimãos e guarda-corpos'), i::__("Banheiros adaptados para PCD's"),
                i::__('Área de alimentação preferencial identificada'),
                i::__("Vagas de estacionamento para PCD's reservadas"),
                i::__("Assentos para pessoas obesas, pessoas com mobilidade reduzida, PCD's e pessoas idosas reservadas"),
                i::__('Filas preferenciais identificadas'), i::__('Iluminação adequada'),
                i::__('Livro e/ou similares em braile'), i::__('Audiolivro'),
                i::__('Uso Língua Brasileira de Sinais - Libras'),
                i::__('Sistema Braille em materiais impressos'),
                i::__('Sistema de sinalização ou comunicação tátil'), i::__('Audiodescrição'),
                i::__('Legendas para surdos e ensurdecidos'), i::__('Linguagem simples'),
                i::__('Textos adaptados para software de leitor de tela'),
                i::__('Capacitação em acessibilidade para equipes atuantes nos projetos culturais'),
                i::__('Contratação de profissionais especializados em acessibilidade cultural'),
                i::__('Contratação de profissionais com deficiência'),
                i::__('Formação e sensibilização de agentes culturais sobre acessibilidade'),
                i::__('Formação e sensibilização de públicos da cadeia produtiva cultural sobre acessibilidade'),
                i::__("Envolvimento de PCD's na concepção do projeto"), i::__('Outras'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedExpectedAccessibilityMeasures') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedHasEnvironmentalPractices', [
            'label' => i::__('A atividade executada contou com medidas ou práticas socioambientais?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedEnvironmentalPracticesDescription', [
            'label' => i::__('Quais medidas e práticas socioambientais foram executadas na atividade?'),
            'type'  => 'text',
            'should_validate' => fn($e) => $e->isMetadataRequired('executedEnvironmentalPracticesDescription') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedHasPressStrategy', [
            'label' => i::__('A atividade executada contou com estratégia de relacionamento com a imprensa?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedHasInnovationAction', [
            'label' => i::__('A atividade executada contou com ação de experimentação/inovação?'),
            'type'  => 'select',
            'options' => ['true' => i::__('Sim'), 'false' => i::__('Não')],
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedInnovationTypes', [
            'label' => i::__('Quais tipos de experimentação/inovação foram executados?'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Uso de novas tecnologias (AR, VR, IA, etc.)'),
                i::__('Novas linguagens artísticas'), i::__('Fusão de linguagens'),
                i::__('Metodologias participativas inovadoras'),
                i::__('Novos modelos de gestão cultural'),
                i::__('Economia criativa e novos modelos de negócio'),
                i::__('Sustentabilidade e práticas ambientais inovadoras'),
                i::__('Inclusão e acessibilidade de forma inovadora'),
                i::__('Experimentação em espaços não convencionais'),
                i::__('Coprodução/cocriação com públicos'), i::__('Outros'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedInnovationTypes') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);

        $app->registerMetadata(new Metadata('executedDocumentationTypes', [
            'label' => i::__('Tipo de documentação produzida (executado)'),
            'type'  => 'multiselect',
            'options' => [
                i::__('Fotografia'), i::__('Vídeo'), i::__('Áudio'),
                i::__('Relatório textual'), i::__('Caderno de processo'),
                i::__('Publicação impressa'), i::__('Publicação digital'),
                i::__('Website/Plataforma online'), i::__('Redes sociais'),
                i::__('Depoimentos'), i::__('Registros de processo'),
                i::__('Acervo digitalizado'), i::__('Não haverá documentação específica'),
                i::__('Outros'),
            ],
            'should_validate' => fn($e) => $e->isMetadataRequired('executedDocumentationTypes') ? i::__('Campo obrigatório') : false,
        ]), $Delivery);
    }
}

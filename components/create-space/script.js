app.component('create-space', {
    template: $TEMPLATES['create-space'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('create-space')
        return { text }
    },

    created() {
        this.iterationFields();
        var stat = 'publish';
    },

    data() {
        return {
            entity: null,
            fields: [],
        }
    },

    props: {
        editable: {
            type: Boolean,
            default: true
        },
    },

    computed: {
        areaErrors() {
            return this.entity?.__validationErrors?.['term-area'];
        },
        areaClasses() {
            return this.areaErrors ? 'field error' : 'field';
        },
        modalTitle() {
            if (this.entity?.id) {
                if (this.entity.status == 1) {
                    return __('espaçoCriado', 'create-space');
                } else {
                    return __('criarRascunho', 'create-space');
                }
            } else {
                return __('criarEspaço', 'create-space');
            }
        },
        otherTypeId() {
            return Utils.getSpaceOtherTypeId();
        },
    },

    watch: {
        'entity.type': {
            handler(newValue, oldValue) {
                if (!this.entity) return;

                const otherTypeId = Utils.getSpaceOtherTypeId();
                const newTypeId = this.getTypeId(newValue);
                const oldTypeId = this.getTypeId(oldValue);

                if (oldTypeId === otherTypeId && newTypeId !== otherTypeId) {
                    this.clearOutroTipoDeEspaco();
                } else if (newTypeId !== otherTypeId && this.entity.informarQualOutroTipoDeEspaco) {
                    this.clearOutroTipoDeEspaco();
                }
            },
            immediate: false
        }
    },

    methods: {
        getTypeId(type) {
            if (!type) return null;
            if (typeof type === 'object' && type.id !== undefined) {
                return Number(type.id);
            }
            return Number(type);
        },

        iterationFields() {
            let skip = [
                'createTimestamp',
                'id',
                'location',
                'name',
                'shortDescription',
                'status',
                'type',
                '_type',
                'userId',
            ];
            Object.keys($DESCRIPTIONS.space).forEach((item) => {
                if (!skip.includes(item) && $DESCRIPTIONS.space[item].required) {
                    this.fields.push(item);
                }
            })
        },
        createEntity() {
            this.entity = Vue.ref(new Entity('space'));
            this.entity.type = 1;
            this.entity.terms = { area: [] }

            this.entity.removeOptions = [
                'Ponto de Cultura',
            ];
        },
        createDraft(modal) {
            this.entity.status = 0;
            this.save(modal);
        },
        createPublic(modal) {
            this.entity.status = 1;
            this.save(modal);
        },
        save(modal) {
            modal.loading(true);
            this.entity.save().then((response) => {
                this.$emit('create', response)
                modal.loading(false);
                Utils.pushEntityToList(this.entity);

            }).catch((e) => {
                modal.loading(false);
            });
        },

        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },

        clearOutroTipoDeEspaco() {
            if (this.entity) {
                this.entity.informarQualOutroTipoDeEspaco = '';

                if (this.entity.__validationErrors && this.entity.__validationErrors.informarQualOutroTipoDeEspaco) {
                    delete this.entity.__validationErrors.informarQualOutroTipoDeEspaco;
                }
            }
        }
    },
});

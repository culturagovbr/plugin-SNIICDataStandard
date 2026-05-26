globalThis.Utils.getEntityTypeName = function (entity, entityType = null) {
    if (!entity || !entity.type) {
        return '';
    }

    entityType = entityType || entity.__objectType;

    if (entityType === 'space') {
        const otherTypeId = $MAPAS?.config?.entityTypes?.space?.otherTypeId ?? 2040;
        if (entity.type?.id === otherTypeId && entity.informarQualOutroTipoDeEspaco) {
            return 'Outros (' + entity.informarQualOutroTipoDeEspaco + ')';
        }
    }

    return entity.type?.name || '';
};

globalThis.Utils.getSpaceOtherTypeId = function (hasStringType = false) {
    const otherTypeId = $MAPAS?.config?.entityTypes?.space?.otherTypeId ?? 2040;
    if (hasStringType) {
        return otherTypeId.toString();
    }
    return otherTypeId;
};

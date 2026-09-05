/**
 * KORRIGIERTES Beispiel:
 *
 * Parent:
 *   ed_ev.id = 9201
 *
 * Child:
 *   ed_ev_info.to_ev_id muss in der NEU-Zeile sofort #9201 anzeigen.
 */
const binding = {
    relationId: "ed_ev__ed_ev_info",
    parentRecordsetId: "rs_ed_ev",
    parentField: "id",
    childRecordsetId: "rs_ed_ev_info",
    childField: "to_ev_id",
    inheritParentValue: true,
    boundFieldReadonly: true
};

let currentParentRecord = {
    id: 9201
};

const childRecordsetRoot =
    document.querySelector(
        '[data-recordset-id="rs_ed_ev_info"]'
    );

// 1. Vorhandene *-Zeile sofort mit #9201 belegen.
DataFormBoundBinding.bindExistingNewRows(
    childRecordsetRoot,
    binding,
    currentParentRecord
);

// 2. Später neu gerenderte *-Zeilen automatisch ebenfalls belegen.
DataFormBoundBinding.observeNewRows(
    childRecordsetRoot,
    binding,
    () => currentParentRecord
);

// 3. Paginierung aus dem horizontalen Bereich herausziehen und
//    immer direkt unter den Datensätzen positionieren.
DataFormRecordsetLayout.observe(
    childRecordsetRoot
);

// 4. Bei Parent-Navigation beides aktualisieren.
function onParentRecordChanged(parentRecord) {
    currentParentRecord = parentRecord;

    DataFormBoundBinding.applyParentChange({
        binding,
        parentRecord,
        recordsetRoot: childRecordsetRoot,
        childNewButton:
            childRecordsetRoot.querySelector(
                '[data-action="new"]'
            ),
        onFilter: filter => {
            ChildRecordset.setFilter(filter);
        },
        onDefaults: defaults => {
            ChildRecordset.setNewDefaults(defaults);
        }
    });

    DataFormRecordsetLayout
        .ensurePaginationBelow(
            childRecordsetRoot
        );
}

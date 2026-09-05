// Anwendungsbeispiel: Recordset "ed_ev"

function afterSaveEdEv(dataformContext) {
    if (!dataformContext.result?.success) {
        DataFormUI.showError(
            dataformContext.result?.message
            ?? "Speichern fehlgeschlagen."
        );
        return;
    }

    const evId = dataformContext.record.id;

    DataFormRuntime.refreshRecordset(
        "rs_ed_ev_info",
        {
            filter: {
                to_ev_id: evId
            }
        }
    );

    DataFormRuntime.setMode(
        dataformContext.dataform.id,
        "show"
    );
}

// zentrale Registrierung
DataFormCallbacks.register(
    "afterSaveEdEv",
    afterSaveEdEv
);

// Recordset-Konfiguration:
// "afterSave": "afterSaveEdEv(dataformContext)"

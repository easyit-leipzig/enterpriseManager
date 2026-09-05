/**
 * DataForm 5 – Layout-Hilfe integrieren
 *
 * Voraussetzung:
 *   assets/js/dataform-help.js
 *   assets/css/dataform-help.css
 */

function activateLayoutHelp() {
    return DataFormHelp.show(
        "dataform.layout",
        "#dataform-help-panel"
    );
}

/**
 * Beispiel:
 * Beim Aufruf der Layout-Seite wird automatisch die passende Hilfe geladen.
 */
document.addEventListener(
    "dataform:pagechange",
    event => {
        if (event.detail?.page === "layout") {
            activateLayoutHelp();
        }
    }
);

/**
 * Optionaler Hilfe-Button auf der Layout-Seite.
 */
document
    .querySelector('[data-help-id="dataform.layout"]')
    ?.addEventListener(
        "click",
        () => DataFormHelp.open("dataform.layout")
    );

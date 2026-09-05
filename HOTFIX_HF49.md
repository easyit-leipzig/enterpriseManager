# HF49 – stabile Paket-Auswahl und CRUD über Paket-ID

HF49 behebt den Fehler, bei dem nach dem Anklicken bzw. Bearbeiten gespeicherter Projektpakete die Meldung „Das gespeicherte Paket wurde nicht gefunden.“ erscheinen konnte und das obere Exportformular nicht zuverlässig mit dem gespeicherten Paketstand gefüllt blieb.

## Änderungen

- Gespeicherte Pakete werden für CRUD-Folgeaktionen über die unveränderliche `packageId` aus `manifest.json` adressiert; der veränderliche Dateiname ist nicht mehr der primäre Schlüssel.
- Klick auf eine Paketzeile lädt weiterhin Paketname, Paketbestandteile, DataForms und Basistabellen in das obere Exportformular.
- Die ausgewählte Paket-ID wird in der URL als `load_package` gespeichert, sodass die Auswahl auch nach einem Reload erhalten bleibt.
- Nach Umbenennen eines Pakets bleibt die Paket-ID stabil und die Folgeaktionen funktionieren mit dem neuen Dateinamen.
- Ein veralteter oder fehlender Paketstand bricht die gesamte Seite nicht mehr ab. Die Paketablage und das Formular bleiben bedienbar und zeigen eine gezielte Fehlermeldung.
- Download, Bearbeiten und Löschen verwenden in der aktuellen Oberfläche die Paket-ID.
- Legacy-Aufrufe über Dateinamen bleiben aus Kompatibilitätsgründen unterstützt.

## Zielzustand

Beim Anklicken von `ed_ev_demo` in „Gespeicherte Pakete“ wird oben exakt dessen gespeicherte Exportkonfiguration geladen. Nach Reload oder Umbenennen bleibt dieser Paketstand über die Paket-ID eindeutig referenzierbar.

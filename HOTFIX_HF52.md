# RC1.8 FC1 – HF52

## Projektpakete: Relationsdetails in bestehender Paketvorschau wiederherstellen

HF52 behebt den Fall, dass `dataform_relations` in der Paketprüfung korrekt mit einer Anzahl (z. B. 4) angezeigt wird, der Detailbereich „Beziehungen und Lookups im Paket“ jedoch leer bleibt. Ursache war eine bereits in der PHP-Session gespeicherte Vorschau aus einem älteren Hotfix, die das neu eingeführte abgeleitete Feld `relations` noch nicht enthielt.

Die Paketvorschau wird jetzt bei jedem Laden aus dem bereits geprüften `.dfpkg` selbst nachhydratisiert. Dazu werden `database/dataforms.json`, `database/dataform_fields.json` und `database/dataform_relations.json` gelesen und die fachlichen Relationstexte neu aufgebaut. Die aktualisierte Vorschau wird anschließend wieder in der Session gespeichert.

Damit erscheinen auch bereits vor HF51 geprüfte Pakete ohne erneuten Upload mit ihren 1:n- und n:1-/Basistabellen-Lookups.

# HOTFIX HF65 – Speichern-Erfolg als modales Dialogfenster

## Ziel

Erfolgreiche Speicheraktionen in DataForm werden nicht mehr als kurz eingeblendeter Toast bzw. HTML-Hinweis dargestellt, sondern als echtes modales JavaScript-Dialogfenster.

## Verhalten

- Bei aktivierter DataForm-Option „JavaScript-Dialogfenster nach dem Speichern anzeigen“ öffnet sich nach erfolgreichem Speichern ein modales `<dialog>`.
- Das Dialogfenster zeigt einen eindeutigen Erfolgsstatus, die konkrete Meldung und eine `OK`-Schaltfläche.
- Das Dialogfenster schließt nicht automatisch; der Benutzer bestätigt mit `OK` oder kann die native Escape-Funktion verwenden.
- Bei deaktivierter Option wird kein Erfolgsdialog angezeigt.
- Validierungs- und Fehlermeldungen bleiben weiterhin dauerhaft im Seiteninhalt sichtbar.
- Das Verhalten gilt fuer manuelles Speichern, Ad-hoc-Speichern und Neuanlagen.

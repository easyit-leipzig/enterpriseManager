# RC1.8-FC1-HF76 PUBLISH2 – eindeutige Aktionsbuttons

PUBLISH2 beseitigt projektweit doppelte Bildbelegungen innerhalb sichtbarer Aktionsgruppen, ohne Funktionen zu entfernen.

## Grundregel

Innerhalb einer zusammengehörigen Aktionsgruppe dürfen zwei fachlich unterschiedliche Aktionen nicht dasselbe Buttonbild verwenden. Echte identische Controls mit gleichem Ziel und gleicher Aktion sind unzulässig. Wiederkehrende Standardaktionen wie Speichern oder Löschen dürfen selbstverständlich auf unterschiedlichen Seiten dasselbe kanonische Bild verwenden.

## Bereinigte Schwerpunkte

- Enterprise-Dashboard: Projekt anlegen, Projekt registrieren, Betriebszentrale, Benutzer & Rechte, Audit, Setup, Datenbank-Assistent und Recovery sind visuell eindeutig.
- Projektverwaltung: Neu, Registrieren und Restore sind eindeutig.
- Security: Benutzer, Rollen und Capabilities sind eindeutig; Audit-Paginierung verwendet Zurück/Weiter.
- DataForm: Formular-Designer/DataForms, Import-Protokoll/Fehlerbericht/Schließen sowie Alle/Keine-Auswahl sind eindeutig.
- Reports: PDF, CSV, Word, HTML und JSON erhalten getrennte zentrale Belegungen.
- Recovery/Setup: Setup und Datenbank-Assistent sowie Prüfen/Reparieren sind getrennt.

## Audit

Der statische Projekt-Audit ergibt 0 Aktionsgruppen mit mehrfach derselben Registry-Belegung und 0 Aktionsgruppen mit identischem Ziel.

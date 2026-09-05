# easyIT Enterprise – Master-Arbeitsstand

**Master-Version:** RC1.7.4-dev-phaseD  
**Basis:** tatsächlich enthaltener Stand des Archivs `easyIT-Enterprise-RC1.4.3-dev.zip`  
**DataForm5-Core:** RC1.7.1-dev-phaseB  
**Enterprise-Anwendung vor Konsolidierung:** RC1.7.2-dev-phaseC

## Zweck

Dieser Stand ist die konsolidierte Basis für die weitere Entwicklung. Er enthält den vollständigen vorhandenen Enterprise-Quellstand einschließlich DataForm und der zentralen Lizenzverwaltung.

## Vorhanden

- Enterprise-Anwendung mit Login, Dashboard, Projekten und Developer-Bereich
- DataForm5-Core
- DataForm-Produkt unter `products/dataform/`
- Datenbankabstraktion und mehrere Datenbanktreiber im Core
- Event-/Plugin-/Modul-Infrastruktur
- Security, Audit, Recovery, Queue, Scheduler, API, Workflow, Hilfe und weitere Core-Dienste
- zentrale Lizenzierungslogik in `DataForm5-Core/system/licensing/`
- Lizenzverwaltung unter `app/licensing/`
- Admin-Schema für Lizenzen unter `installer/schema/admin/004_licensing.php`

## Bewusst nicht vorgetäuscht

Die folgenden früher geplanten bzw. separat entwickelten Produkte sind in diesem Master nicht enthalten und wurden nicht als leere Scheinimplementierungen ergänzt:

- easyIT Dialog
- easyIT Nachhilfe
- CSV-Engine als eigenständiges Produkt
- Designer/Export/API/Themes als eigenständige globale Enterprise-Module

Sie sollen später nur aus einem verifizierten realen Quellstand übernommen werden.

## Bereinigung dieser Konsolidierung

Entfernt wurden ausschließlich reproduzierbare Laufzeit- und Testartefakte:

- Recovery-Testverzeichnisse
- Test-Runtime-Dateien
- Scheduler-History-Logs
- temporäre Recovery-Testpakete

Release-/Testberichte wurden beibehalten, da sie Entwicklungsnachweise darstellen.

## Versionsprinzip

Die Master-Version beschreibt die Enterprise-Suite. Komponenten behalten eine eigene Versionsnummer, bis ihre API oder ihr Inhalt tatsächlich geändert wird. Deshalb wird der DataForm5-Core nicht künstlich auf die Master-Version umnummeriert.

## Weiterarbeitsregel

1. Dieser Master ist ab jetzt die Basis.
2. Änderungen werden immer in den vollständigen Projektstand integriert.
3. Ausgelieferte ZIPs enthalten immer das gesamte Projekt.
4. Keine Overlay-ZIPs als reguläre Entwicklungsstände.
5. Fehlende Produkte werden nur aus realen, geprüften Quellständen integriert.


## Phase E

Das Modul-SDK standardisiert die Erstellung und Validierung neuer Enterprise-Module.

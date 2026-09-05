
## RC1.8-FC1-HF76-FIX6

- DataForm-Listenaktion „Datensätze anzeigen“ auf `anzeigen.png` korrigiert; „DataForm öffnen“ bleibt `formular.png`.
- DS-Buttonbilder sind ausschließlich für echte Datensatznavigation zugelassen.
- Projektweiter hochspezifischer Buttonset-Reset entfernt historische CSS-Hintergründe, Rahmen und Schatten hinter Registry-PNGs.
- Zentrale Titel/ARIA-Texte bleiben alleinige Registry-Metadatenquelle.

# HF76 FIX1 – DataForm Include-Idempotenz

- Behebt den Fatal Error `Cannot declare class DataFormFieldTypeRegistry, because the name is already in use`.
- Ursache war eine doppelte Einbindung von `DataFormFieldTypes.php` über `DataFormManager.php` und nachfolgende normale `require`-Aufrufe in HTTP-Einstiegspunkten.
- Alle DataForm-HTTP-Einstiegspunkte laden PHP-Abhängigkeiten nun konsequent mit `require_once`.
- Neuer Regressionstest reproduziert die problematische Include-Reihenfolge und prüft alle DataForm-Einstiegspunkte auf ausführbare einfache `require`-Anweisungen.

# HF75 – zentrales grafisches Buttonset

- Das vom Benutzer bereitgestellte `assets/img/buttonset.png` ist die kanonische Quelle für Neu, Bearbeiten, Löschen, Speichern und Datensatzzeiger.
- CRUD-Schaltflächen verwenden die grafischen Buttons aus dem Set anstelle der bisherigen generierten 3D-Symbole.
- Datensatzzeiger verwenden jetzt exakt: aktueller Datensatz = Dreieck, neuer Datensatz = Stern, normaler Datensatz = leerer Button.
- Die bestehende Server-/JavaScript-Logik bleibt unverändert; nur die Darstellung wurde zentral ersetzt.
- Einzelne verlustfreie Ausschnitte liegen unter `assets/img/buttonset-icons/` und werden vom globalen CRUD-Stylesheet verwendet.

# HF74 – Löschbutton je Datensatzzeile

- Jede bestehende DataForm-Datensatzzeile besitzt jetzt einen direkten Löschbutton.
- Die Löschung nutzt die bestehende Referenz- und Kinddatensatzprüfung.
- Die `*`-Neuzeile besitzt keinen Löschbutton.
- Inline-Speichern, Anzeigen und Sammellöschung bleiben unverändert.

# HF73 – Projekt-Restore aus ZIP-Sicherung

- Neuer Restore-Assistent unter Enterprise → Projekte.
- HF72-Projekt-ZIPs werden geprüft und können inklusive Projektregistrierung und Projektdatenbank wiederhergestellt werden.
- Automatische Wiederverwendung einer noch vorhandenen Originaldatenbank oder Neuaufbau aus `database.sql`.
- Explizite Restore-Strategien für Wiederverwenden, Neuaufbau und destruktives Ersetzen einer Zieldatenbank.
- Sessiongeschützte Restore-Vorschau, SHA-256-Prüfung, Schutz von System-/Enterprise-Datenbanken sowie Audit/Event-Integration.

# HF72 – Projektsicherung vor Projektlöschung

- Projekt kann vor dem Löschen als sessiongeschütztes ZIP mit Projektmetadaten und vollständigem SQL-Dump gesichert werden.
- Sicherungsoption ist standardmäßig aktiviert; bei Sicherungsfehler wird nichts gelöscht.
- Downloadlink bleibt 24 Stunden gültig und wird nach erfolgreicher Löschung in der Projektliste angeboten.
- Datenbanklöschung bleibt eine separate, standardmäßig deaktivierte Option.
- Backup-Audit enthält Dateiname, SHA-256, Größe und Datenbankstatistik.

# HF67

- Inline-Neuanlage (`*`) nur noch auf der letzten Seite der Datensatzliste.
- „Neu“ und „+ Neuer Datensatz“ springen zum Tabellenende.
- Leere Tabellen behalten die Inline-Neuzeile auf Seite 1.

## RC1.8-FC1-HF64 – Zentrales easyIT-DataForm-Branding

- Das bereitgestellte easyIT-DataForm-Logo wird auf allen DataForm-Seiten zentral unterhalb der Enterprise-Kopfleiste eingeblendet.
- Die Einbindung gilt sowohl für Workspace-/CRUD-Seiten als auch für den DataForm-Runtime-Shell.
- Exportierte `.dfpkg`-Projektpakete enthalten das Logo zusätzlich unter `assets/branding/easyit-dataform-logo.png`.
- Paketprüfung akzeptiert ausschließlich diese definierte Branding-Datei und validiert PNG-Signatur sowie Größenlimit.
- Importierte Pakete verwenden auf einem HF64-Zielsystem automatisch dasselbe zentrale DataForm-Branding.

## HF61

## RC1.8-FC1-HF63 – Speichern-Erfolg als JavaScript-Toast

- DataForm-Einstellung präzisiert zu „JavaScript-Erfolgsmeldung nach dem Speichern anzeigen“.
- Positive Record-Speichermeldungen werden als nicht-modaler, automatisch ausblendender Toast dargestellt.
- Validierungs- und Fehlermeldungen bleiben unverändert sichtbar.
- Gilt gleichermaßen für manuelles und Ad-hoc-Speichern.


- Bestehende Datensätze sind direkt in der tabellarischen Datenblattansicht editierbar.
- Pro Datensatzzeile gibt es typgerechte Eingabefelder und einen eigenen Speichern-Button.
- Lookup-Felder bleiben echte Select-Felder; Validierungsfehler verbleiben in der betroffenen Zeile.
- Nach Inline-Speichern bleibt die Liste offen und der Datensatzzeiger auf dem gespeicherten Datensatz aktiv.

## HF60

- Tabellengebundene DataForms zeigen in der Datensatz-Datenblattansicht jetzt immer alle physischen DataForm-Felder.
- Die bisherige Standardbegrenzung auf vier Listenspalten entfällt für physische Tabellen.
- Gespeicherte/alte Listenspalten-Einstellungen können bei physischen Tabellen keine realen Spalten mehr ausblenden.
- Die `*`-Neuzeile enthält dadurch ebenfalls alle Tabellenfelder, einschließlich Lookup-/Pflichtfelder wie `from_person` und `to_person`.
- Die Listenspalten-Konfiguration kennzeichnet physische Tabellen als vollständig und verhindert dort das Ausblenden einzelner Tabellenfelder.

## HF59
- Inline-Validierungsfehler in der tabellarischen Neuanlage stabilisiert; Listenansicht und Sortierzustand bleiben erhalten.

# HF55 – Feldtypgerechte tabellarische Datensatz-Ausgabe

- Datensatzlisten rendern Feldwerte wieder in ihrer Feldsemantik statt als reine Textzellen.
- Text, Zahl, Datum/Uhrzeit, E-Mail und URL erscheinen als schreibgeschützte HTML-Inputs.
- Textarea, Checkbox, Select und relationale Lookup-Felder erhalten passende read-only/disabled Ausgabekomponenten.
- 1:n-/n:1-Lookups zeigen den aufgelösten Anzeigewert in einer deaktivierten Auswahl an; die gespeicherte ID bleibt unverändert.
- CSV-Export und Bearbeitungsformulare bleiben unverändert.

## RC1.8-FC1-HF54

- DataForm-Projektpakete: Exportformular behält DataForm-/Tabellen-Auswahl nach Export.
- Gespeicherte Paketstände sind über echten Paketnamen-Link und Zeilenklick ladbar.
- Export-Reload erfolgt stabil über `packageId`.

## HF53 – Self-contained package import into empty projects
- Bootstrap DataForm metadata tables before importing package configuration.
- Reject non-empty metadata sections when their target schema cannot be provided instead of silently skipping them.
- Verify imported DataForm metadata and refresh the project-package catalog immediately after import.

## HF52 – Project package preview relation hydration
- Rebuild relation details from the checked archive when session preview data is stale.
- Keep relation count and human-readable relation list consistent.

## HF50 – Projektpaket-CRUD resilient gegen veraltete Paketverweise

- Paketaktionen lösen stabile ID und Dateiname robust auf.
- DELETE ist bei bereits fehlender Datei idempotent.
- CRUD nutzt Post/Redirect/Get und entfernt stale `load_package`-Queryzustände.

# HF48

- Klick auf eine Zeile unter „Gespeicherte Pakete“ lädt die persistierte Paketkonfiguration zurück in das obere Exportformular.
- Geladen werden Paketname, Paketbestandteile, ausgewählte DataForms und physische Basistabellen.
- Die Konfiguration wird direkt aus `manifest.json` (`includes`/`selection`) des gespeicherten `.dfpkg` gelesen.
- Die gewählte Paketzeile wird markiert; eine sichtbare Meldung bestätigt den geladenen Paketstand.
- CRUD-Schaltflächen bleiben von der Zeilenaktion getrennt; Tastaturbedienung mit Enter/Leertaste ist möglich.

# HF47

- Gespeicherte `.dfpkg`-Pakete erhalten eine eigene physische Paketliste mit vollständigem CRUD.
- CREATE erfolgt über den bestehenden Paketexport; nach dem Download aktualisiert sich die Paketliste automatisch. READ erfolgt über direkten erneuten Download.
- UPDATE benennt Paket und Datei konsistent um und aktualisiert `manifest.json`, `project.json` und `README.txt`.
- DELETE entfernt nur die gespeicherte Paketdatei; die Paket-Historie bleibt als Auditnachweis erhalten.
- Update- und Delete-Aktionen werden in `project_package_history` protokolliert.
- Alle Paket-CRUD-Aktionen verwenden die globalen 3D-CRUD-Buttons.

# HF46

- Paket-/Projektname im Paketexport sichtbar gemacht.
- Live-Vorschau und persistente Eingabe ergänzt.
- Paketname in Paket-Historie aufgenommen.

# HF45

- Projektpakete: optionalen eigenen Paketnamen neben dem automatischen Standardnamen ergänzt.
- Eigener Paketname wird als Anzeigename in Manifest/Projektmetadaten gespeichert und als sicher normalisierte Dateibasis verwendet.
- Importvorschau zeigt den Paketnamen.

## RC1.8-FC1-HF44

- Projektpaket-Export besitzt jetzt sichtbare, getrennte Auswahlmöglichkeiten für DataForms, Beziehungen/Lookups, Tabellenbindungen, physische Tabellenschemata, Workflows/Regeln, Module und Beispieldaten.
- DataForms und Basistabellen können einzeln für ein Paket ausgewählt werden.
- Physische Anwendungstabellen werden als `schema/<tabelle>.sql` portabel mitgeführt und beim Import auf einem leeren Zielprojekt angelegt.
- Reale Beispieldaten ausgewählter Basistabellen werden optional als `records/<tabelle>.json` exportiert; IDs bleiben erhalten, damit 1:n- und Lookup-Referenzen konsistent bleiben.
- Gebundene Tabellen und Ziele von Basistabellen-Lookups werden als Paketabhängigkeiten automatisch ergänzt.
- Importvorschau trennt Konfiguration, physische Tabellenschemata und Beispieldaten.
- Paketformat auf 1.1 erweitert; ältere boolesche Exportaufrufe bleiben kompatibel.

## RC1.8-FC1-HF43

- n:1-/Lookup-Beziehungen können ihre Referenzwerte jetzt direkt aus einer physischen Basistabelle beziehen.
- Neue Lookup-Quelle-Auswahl: `DataForm` oder `Basistabelle`.
- Für Basistabellen werden Tabelle, Schlüsselspalte und Anzeigespalte getrennt konfiguriert.
- Reine Stamm-/Typ-/Status-/Wertetabellen benötigen dadurch kein zusätzliches DataForm.
- Runtime liest Lookup-Optionen direkt aus der Basistabelle, validiert gespeicherte Schlüssel und löst Anzeigewerte in Listen/Details auf.
- Bestehende DataForm-Lookups bleiben kompatibel.

## RC1.8-FC1-HF39

- Beziehungsdesigner um `n:1 / Lookup` erweitert.
- Lookup-Richtung wird fachlich als Ausgangs-DataForm → Referenz-DataForm modelliert.
- `source_field_id` speichert das Zuordnungsfeld des Ausgangs-DataForms.
- Runtime rendert n:1-Zuordnungen als Auswahlfelder und zeigt konfigurierte Lookup-Bezeichnungen in Listen/Details.
- Lookup-Auswahl wird beim Speichern auf Existenz geprüft; referenzierte Lookup-Datensätze werden gegen Löschen geschützt.

## RC1.8-FC1-HF38
- Tabellengebundene DataForms werden vor der Relationsprüfung mit der realen physischen Tabellenstruktur synchronisiert.
- Fehlende reale Spalten werden automatisch in `dataform_fields` registriert; bestehende passende Felder erhalten fehlende `table_binding`-Metadaten ohne Duplikate.
- Der Beziehungsdesigner kann dadurch vorhandene FK-Spalten wie `ed_ev_info.to_ev_id` zuverlässig auswählen; Phantomfelder bleiben gesperrt.

## RC1.8-FC1-HF37
- Beziehungsdesigner: bestehende Beziehungen können vollständig bearbeitet werden.
- 1:n-Fremdschlüssel: bei tabellengebundenen DataForms werden nur tatsächlich vorhandene physische Kindspalten angeboten.
- Fehlerhafte Phantom-Zuordnungen werden automatisch eindeutig repariert oder vorsorglich deaktiviert und sichtbar als fehlerhaft markiert.
- Neue Kind-Fremdschlüsselfelder werden nur noch ausdrücklich angelegt; bei Tabellenbindung wird die physische Spalte real erzeugt.

## RC1.8-FC1-HF36
- Neuer Feldtyp `derived_multienum`: dynamische Mehrfachauswahl aus einer Projekttabelle, CSV-Speicherung stabiler IDs/Werte, Label-Auflösung, Abhängigkeitsfilter und Referenzschutz.

## RC1.8-FC1-HF35
- Global CRUD 3D UI: zentrale CSS-Einbindung für Enterprise und DataForm; Create/Read/Edit/Save/Delete werden grafisch und farblich konsistent dargestellt.

## RC1.8-FC1-HF34
- Tabellengebundene DataForms: Datensatz-CRUD arbeitet direkt auf der physischen Projekttabelle.
- Master/Detail: Eltern-Detail zeigt 1:n-Kinddatensätze; Kind-Neuanlage übernimmt den Fremdschlüssel serverseitig.
- DataForm-Liste: ungebundene Projekttabellen können direkt als DataForm erzeugt werden.

## RC1.8-FC1-HF33
- 1:n-Beziehungen: verbindliche Eltern.id → Kind.Fremdschlüssel-Semantik; Elternauswahl nur im Kindformular; Legacy-Beziehungen werden soweit eindeutig automatisch repariert.

## RC1.8-FC1-HF32
- Tabellen/Feld-CRUD: reale Spaltenreihenfolge mit ↑/↓ ändern; id bleibt fix und gebundene DataForm-Feldpositionen werden synchronisiert.

## RC1.8-FC1-HF31
- Tabellen/DataForms: DataForm direkt aus einer verwalteten Projekttabelle erzeugen; persistente Tabellenbindung und automatische Feldableitung.

## RC1.8-FC1-HF30
- DataForm-Liste: vollständige Löschfunktion mit Doppelbestätigung und transaktionaler Bereinigung abhängiger DataForm-Strukturen.

## RC1.8-FC1-HF29
- DataForm Beziehungen/Module/Projektpakete: Legacy-Renderer entfernt; aktuelle Enterprise-Shell inklusive Workspace-CSS angebunden.

## RC1.8-FC1-HF28
- Tabellen/Feld-CRUD: datentypabhängige Optionen; unzulässige Defaults, Extras und Indizes werden ausgeblendet und bei Altzuständen automatisch mit Hinweis zurückgesetzt.

## RC1.8-FC1-HF27
- Tabellen/Feld-CRUD: editierbarer Vorgabewert, verwalteter INDEX/UNIQUE INDEX und Extra-Mehrfachauswahl (UNSIGNED, ZEROFILL, AUTO_INCREMENT, ON UPDATE CURRENT_TIMESTAMP).

## RC1.8-FC1-HF26
- DataForm Tabellen: vollständiges CRUD für Felder/Spalten DataForm-verwalteter Projekttabellen; id, Schlüssel/Beziehungen, Systemtabellen und externe Quellen bleiben geschützt.

## RC1.8-FC1-HF25
- DataForm Tabellen: echter Tabellen-Explorer für Projekt-DB, MySQL, SQLite, CSV und Oracle; sichere Verwaltung eigener Projekttabellen.

## RC1.8-FC1-HF24
- DataForm-Datenquellen: Secret-Schlüssel werden bei Installation, Reparatur oder erster Nutzung automatisch sicher bereitgestellt.

## RC1.8-FC1-HF23
- DataForm Datenquellen: vollständiges CRUD, verschlüsselte Kennwörter und Verbindungstest für MySQL/MariaDB, SQLite, CSV und Oracle.

## RC1.8-FC1-HF22
- DataForm Feldmodell: Standardwertquelle „Wert aus Eltern-DataForm“ über 1:n-Elternbeziehung und konkretes Elternfeld.

## RC1.8-FC1-HF21
- DataForm Feldmodell: Standardwertquellen Wie definiert, CURRENT_TIMESTAMP und registrierte JavaScript-Funktion/Provider.

## RC1.8-FC1-HF20
- DataForm Feldeditor: Bereich „Erweitert“ mit erweiterten Feld-, Validierungs-, Eingabe- und Listenoptionen.

## RC1.8-FC1-HF19
- DataForm Designer: numerische Breiten-Schlüssel vor HTML-Escaping und Vergleich explizit in String konvertiert.

## RC1.8-FC1-HF18
- DataForm Render Boundary Repair und defensive Feld-Konfigurationsnormalisierung.

## RC1.8-FC1-HF17
- DataForm Control-Flow Repair: eindeutige runtime.php, direkter Enterprise-Link, Apache-Rewrite und index-Kompatibilitätsredirect.

## RC1.8-FC1-HF16
- DataForm Frontcontroller rendert Runtime-Shell direkt; Runtime-Proof-Endpunkt und sichtbarer HF16-Marker ergänzt.

## RC1.8-FC1-HF15
- Installer: POST-Werte bei Fehlern erhalten; Augenfunktion für Passwortfelder.

## RC1.8-FC1-HF14
- DataForm Runtime Layout Repair mit eigener, pfadrobuster Workspace-Shell und eigenem Stylesheet.

## RC1.8-FC1-HF13
- DataForm UI/Layout Integration gehärtet; CSS-Fallback und aktuelle Layout-API für Workflow-Seiten.

## RC1.8-FC1-HF12
- Administration & Setup Recovery: permanente Setup-/DB-Assistent-Einstiege und reparierbare fehlende .env.

## RC1.8-FC1-HF11
- Vollständige Projekt-Provisionierung mit Datenbankanlage, Projektschema und automatischer Registrierung ergänzt.
- Bestehende Projektregistrierung bleibt als separater Pfad erhalten.

## RC1.8-FC1-HF10
- Audit Viewer mit Filtern, Suche, Pagination und sicherer Kontext-Redaction ergänzt.

## RC1.8-FC1-HF9
- Benutzer-CRUD, Rollen-CRUD und Capability-Zuweisung im Enterprise-Dashboard ergänzt.
- Schutz des letzten aktiven Administrators, CSRF und Audit ergänzt.

# RC1.8-FC1 / internal phase68

- Phase 6.8: Final-Candidate-Konsolidierung und Feature Freeze.

# RC1.8.6-dev-phase67

- Phase 6.7: Performance-Audit und finales kombiniertes Release-Gate.

# RC1.8.6-dev-phase66

- Phase 6.6: Production-Security-Gate, produktive ENV-Vorlage, Session-Härtung und Entfernung der lokalen `.env` aus dem Release.

# RC1.8.6-dev-phase65

- Phase 6.5: Release-Manifest, Reproduzierbarkeit und konsequenter Ausschluss flüchtiger Runtime-Artefakte.

# RC1.8.6-dev-phase64

- Phase 6.4: Upgrade-/Migration-Abnahme mit vollständigem Migration Ledger, Checksum-Schutz, Idempotenz und Manifest.

# RC1.8.6-dev-phase63

- Phase 6.3: Installer- und statische Fresh-Install-Abnahme.

# RC1.8.6-dev-phase62

- Phase 6.2: Cross-Component-Abnahme.

# RC1.8.6-dev-phase61

- Phase 6.1: Gesamtintegrations-Baseline und Release-Gate; temporäre Root-Datei entfernt.

# RC1.8.5-dev-phase512

- Phase 5.12: zentrales Developer Dashboard; Phase 5 Developer Mode & SDK-Konsolidierung abgeschlossen.

# RC1.8.5-dev-phase511

- Phase 5.11: konsolidierte SDK-Entwicklerdokumentation.

# RC1.8.5-dev-phase510

- RC1.8 Phase 5.10: Developer Quality Center mit gruppierter Gesamt-Regression und CLI-Release-Gate.

# RC1.8.5-dev-phase59

- RC1.8 Phase 5.9: validierte Modul-Paketierung mit root-Struktur, Paketmanifest, SHA-256 und PharData-Fallback ohne ext-zip.

# RC1.8.5-dev-phase58

- Phase 5.8: SDK-Modul-Quality-Gate und module:validate.

# RC1.8.5-dev-phase57

- RC1.8 Phase 5.7: vollständiger Modul-Codegenerator und make:crud.

# RC1.8.5-dev-phase56

- RC1.8 Phase 5.6: SDK-Konsole und make:*-Generatoren.

# RC1.8.5-dev-phase55

- RC1.8 Phase 5.5: Request Profiler mit zentralem ProfilerHub und Core-Instrumentierung.

# RC1.8.5-dev-phase54

- RC1.8 Phase 5.4: Hook Inspector für Lifecycle-/Plugin-Hooks.

# RC1.8.5-dev-phase53

- RC1.8 Phase 5.3: Event Inspector; Enterprise-Eventbus und Inspector auf dieselbe Dispatcher-Instanz vereinheitlicht.

# RC1.8.5-dev-phase52

- RC1.8 Phase 5.2: Service Container Inspector mit Alias-, Singleton-, Resolved- und Dependency-Analyse.

# RC1.8.5-dev-phase51

- RC1.8 Phase 5.1: Developer Mode mit Runtime-/Container-/Modul-/Event-/Queue-/Scheduler-Diagnose und Ctrl+Shift+D Overlay.

# RC1.8.4-dev-phase4

- RC1.8 Phase 4: Enterprise Control Center und konsolidierte Betriebsnavigation.

# RC1.8.3-dev-phase3

- RC1.8 Phase 3: Installer 2.0 mit Admin-DB, Erstadmin, Produktauswahl, Health-Gate und Install-Lock.

# RC1.8.2-dev-phase2

- RC1.8 Phase 2: Performance- und Cache-Konsolidierung mit Modul-Discovery-Fingerprint und Registry-Memoization.

# RC1.8.1-dev-phase1

- RC1.8 Phase 1: Core-Konsolidierung, zentrale Provider-/Namespace-Registrierung und Entfernung eines ausgelieferten Smoke-Testmoduls.

# RC1.7.26-dev-phaseZ

- Phase Z: signierte Cluster-Heartbeats/Replikationsereignisse, Trust Store und Replay-Schutz.

# RC1.7.25-dev-phaseY

- Phase Y: kontrollierte Cluster-Replikation über Shared Storage mit Integritätsprüfung.

# RC1.7.24-dev-phaseX

- Phase X: Shared Storage und providerfähiger StorageManager.

# RC1.7.23-dev-phaseW

- Phase W: verteilte DatabaseQueue und clusterweite Mutex-/Lock-Abstraktion.

# RC1.7.22-dev-phaseV

- Phase V: Cluster Foundation mit Node-Registry, Heartbeats, Leader-Ermittlung und Cluster-Health.

# RC1.7.21-dev-phaseU

- Phase U: Enterprise Monitoring, Health-Snapshots, Heartbeats und Alert-Schwellen.

# RC1.7.20-dev-phaseT

- Phase T: grafische Queue-/Worker-/Scheduler-Verwaltung mit Failed-Job-Retry.

# RC1.7.19-dev-phaseS

- Phase S: Modul-Hintergrundjobs, Queue-Worker und Scheduler-Integration.

# RC1.7.18-dev-phaseR

- Phase R: Modul-Lifecycle, Hooks, Event- und Lifecycle-Logging.

# RC1.7.17-dev-phaseQ

- Phase Q: Modul-API und JSON-Endpunkte.

# RC1.7.16-dev-phaseP

- Phase P: deklarative Modulformulare, automatische CSRF-Prüfung, Validierung und Wiederbefüllung.

# RC1.7.15-dev-phaseO

- Typisierte Modul-Requests auf Basis von `DataForm5\Http\Core\Request`.
- Query-, Body-, JSON- und Datei-Eingaben zentral verfügbar.
- Request-Attribute für aktuellen Benutzer und aufgelöste Modulroute.
- `Request::validate()` bindet die bestehende Validation-Schicht ein.
- `Response::page()`, JSON-, Redirect-, Text- und No-Content-Responses.
- HTTP-422-Fehlerbehandlung für Validierungsfehler.
- Automatischer Legacy-Adapter für Phase-N-Controller mit `array $request`.
- Module-SDK erzeugt ab Phase O typisierte Controller.
- Regressionstest `tests_phase_o_module_http.php`.

# RC1.7.14-dev-phaseN

- Zentrales Modul-Routing mit `ModuleRouteRegistry` und `ModuleRouteDispatcher`.
- Modulrouten deklarieren Controller, HTTP-Methoden und Capabilities im `module.json`.
- Neuer zentraler Einstiegspunkt `app/module.php`.
- Module-SDK erzeugt Controller und routenbasierte UI-Links automatisch.
- Direkte Dateipfade aus URL-Parametern werden nicht verwendet.
- Regressionstest `tests_phase_n_module_routing.php`.

## RC1.7.12-dev-phaseL
- Capability-System für Module und Rollen ergänzt.
- Rollen-Capability-Zuordnung und Admin-Oberfläche ergänzt.
- Modulmanifeste können `capabilities` deklarieren.

# CHANGELOG

## RC1.7.1-dev-phaseB – Dependency Injection & Service Container

- stabiler `ContainerInterface` als öffentlicher Vertrag für Core und Module
- Constructor Injection und Interface-Bindings bleiben vollständig kompatibel
- neue Service-Aliase und Service-Tags
- `make()` mit benannten Parameter-Overrides
- `call()` für automatische Methoden- und Callable-Injection
- Erkennung zirkulärer Abhängigkeiten und Alias-Schleifen
- Container-Diagnose unter `app/developer/container.php` mit rechter Kontexthilfe
- gemeinsamer `enterprise_container()` für die Enterprise-Schicht
- keine Datenbank- oder Schemaänderung

## RC1.7.0-dev-phaseA – Enterprise Event Platform

- zentrale Enterprise-Event-Fassade auf Basis des bestehenden DataForm5-Core EventDispatcher
- stabiler Ereigniskatalog mit dokumentierten Namen
- NamedEvent mit Payload, Kontext und Zeitstempel
- Listener-Prioritäten und stoppbare Events bleiben kompatibel
- erste Core-Ereignisse für Login, Logout, Projektregistrierung und DataForm-Datensatzänderungen
- redigiertes Diagnoseprotokoll unter storage/logs/events.log
- Entwickleransicht app/developer/events.php mit rechter Kontexthilfe
- keine neue Datenbank und keine Schemaänderung

# RC1.6.0-dev – Phase 22

## Report Designer

- Berichte aus DataForms oder gespeicherten Visual Queries anlegen.
- Berichtselemente: Titel, Text, Tabelle, Diagramm, Kennzahl, Trennlinie und Seitenumbruch.
- Live-HTML-Vorschau im DataForm Workspace.
- Druckansicht mit browsergestütztem PDF-Export.
- Exporte als CSV, Word-kompatibles HTML, HTML und JSON.
- Neue Tabellen: `reports`, `report_pages`, `report_elements`, `report_parameters`, `report_exports`.
- Explorer und zentrale Kontexthilfe erweitert.
- Keine neue Datenbank und keine Neuinitialisierung vorhandener Projektdaten.

## RC1.6.1-dev – Phase 23

- REST API Designer für DataForms
- versionierte APIs und konfigurierbare Endpunkte
- Bearer-API-Schlüssel mit sicherer Hash-Speicherung
- Rate-Limits je Endpunkt und Schlüssel
- OpenAPI-3.0-Export
- öffentlicher API-Runtime-Endpunkt für List, Detail, Create, Update und Delete
- API-Aufrufprotokoll mit Status und Laufzeit

## RC1.7.3-dev-master – Master-Konsolidierung

- tatsächlichen RC1.7.2-Enterprise-Stand als Masterbasis festgeschrieben
- DataForm5-Core und DataForm-Produkt vollständig beibehalten
- zentrale Lizenzverwaltung beibehalten und als Bestandteil des Masterstands dokumentiert
- reproduzierbare Test-/Recovery-/Scheduler-Laufzeitreste entfernt
- `modules/` und `packages/` mit klaren Verantwortlichkeiten dokumentiert
- Master-State-Dokumentation und automatischer Master-Strukturtest ergänzt
- veraltete README auf den tatsächlichen Entwicklungsstand aktualisiert

## RC1.7.6-dev-phaseF
- Modul-Paketinstaller für ZIP-Module ergänzt.
- Sichere ZIP-Extraktion mit Path-Traversal-, Größen- und Dateianzahlprüfung.
- Installation, Update mit Rollback und Deinstallation mit Abhängigkeitsprüfung.
- Persistentes Enterprise-Modulregister `modules/.installed.json`.
- CLI-Werkzeuge für Paketbau, Installation, Update und Entfernung.
- Phase-F-Test und Dokumentation ergänzt.

## RC1.7.8-dev-phaseH
- Zentralen Modulkatalog mit Version, Herkunft, Status und Abhängigkeiten ergänzt.
- Verfügbare Modul-ZIPs aus `packages/` werden in der Adminoberfläche aufgeführt.
- Persistente Installationshistorie für Install, Update, Remove, Enable, Disable und Fehler ergänzt.
- CLI-Modulaktionen schreiben ebenfalls in die Historie.
- Phase-H-Test und Dokumentation ergänzt.

## RC1.7.9-dev-phaseI
- Semantische Modulversionsregeln und Core-Kompatibilitätsprüfung.
- Dependency-Versionen bleiben im Manifest erhalten.
- Modulkatalog erkennt kompatible Updates aus packages/.

## RC1.7.10-dev-phaseJ
- Versionierte Datenbankmigrationen je Enterprise-Modul.
- SHA-256-Integritätsprüfung und Batch-Protokollierung.
- Automatische Migration bei Modulinstallation/-update über den Enterprise-Admin.
- Kontrollierter Rollback des letzten Migrations-Batches per CLI.
- Modul-SDK erzeugt `database/migrations/` standardmäßig.
- Migrationsstatus im Modulkatalog sichtbar.

## RC1.7.11-dev-phaseK
- Typisierte Modulkonfiguration mit `config/schema.php`.
- Enterprise-, Produkt- und Projekt-Scopes für Laufzeitwerte.
- Getrennte, AES-256-GCM-verschlüsselte Speicherung von Secrets.
- Grafische Modulkonfiguration unter `app/modules/config.php`.
- Module-SDK erzeugt standardmäßig ein Konfigurationsschema.
- Phase-K-Regressions- und Strukturtest ergänzt.

## RC1.7.13-dev-phaseM
- Deklarative Modulnavigation und Dashboard-Kacheln mit Capability-Filter.
- Zentrale ModuleUiRegistry.
- Module SDK erzeugt UI-Metadaten.

## RC1.8-FC1-HF4
- Globalen Logout-Link für alle authentifizierten `app_nav`-Seiten verfügbar gemacht.
- Logout ist nicht mehr von der Verfügbarkeit der Modul-UI-Navigation abhängig.
- Dashboard-Regressionstest um globalen Logout-Nachweis erweitert.

## RC1.8-FC1-HF6
- Recovery Console um vollständiges Ein-Datei-Backup unter `backup/` erweitert.
- Vollständiger Restore von Projektdateien, `.env`, Install-Lock und easyIT-Datenbanken ergänzt.
- Admin-Passwort bleibt über den wiederhergestellten `users.password_hash` gültig.
- Backup-Ordner gegen HTTP-Zugriff geschützt und Runtime-Backup-ZIPs aus dem Release-Manifest ausgeschlossen.


## RC1.8-FC1-HF40
- Feld-CRUD für bestehende Projekt-Anwendungstabellen aktiviert.
- Interne Systemtabellen bleiben geschützt.
- DataForms können direkt aus bestehenden Anwendungstabellen erzeugt werden.

## RC1.8-FC1-HF41
- Physische SQL-Datentypänderungen tabellengebundener DataForms werden in `dataform_fields.field_type` nachgeführt.
- `table_binding.sql_type` wird bei Schemaänderungen aktualisiert.
- Feldbearbeitung synchronisiert das gebundene DataForm sofort; DataForm-Öffnen führt zusätzlich einen Schemaabgleich aus.
- Benutzerdefinierte Spezialfeldtypen bleiben vor unnötigem Überschreiben geschützt.

## RC1.8-FC1-HF42
- Beziehungsdesigner mit kontextabhängigen, fachlich eindeutigen Feldbeschriftungen und Hilfetexten erweitert.
- 1:n und n:1/Lookup unterscheiden nun explizit Speicherfeld, Referenzschlüssel und sichtbares Anzeigefeld.
- Nicht relevante Eingabefelder werden je Beziehungstyp zuverlässig ausgeblendet.
## HF51 – Detaillierte Relationsvorschau bei Projektpaketen

- Paketprüfung zeigt Beziehungen/Lookups nicht mehr nur als Anzahl in `dataform_relations`.
- Neue Detailansicht mit Name, Typ, Ausgang, Ziel, konkreter Zuordnung, Anzeigefeld und Status.
- Basistabellen-Lookups werden fachlich als `Feld → Tabelle.Schlüssel` inklusive Anzeigespalte dargestellt.
- Paketformat bleibt kompatibel zu 1.1.


## RC1.8-FC1-HF58
- Neuen Datensatz direkt als editierbare `*`-Zeile in der tabellarischen DataForm-Datensatzansicht ergänzt.
- Typgerechte Inline-Eingabeelemente einschließlich Lookup-Auswahlfeldern.
- Inline-Anlage bleibt nach dem Speichern in der Tabellenansicht und markiert den neuen Datensatz aktiv.
- Separate Link-Zeile „Neuen Datensatz anlegen“ aus dem Tabellenkörper entfernt.

## RC1.8-FC1-HF62 – DataForm-Speicherverhalten konfigurierbar

- DataForm → Verhalten enthält jetzt eine DataForm-weite Einstellung für die tabellarische Inline-Bearbeitung: `manual` (erst über „Speichern“) oder `adhoc` (automatisch nach Feldänderung/Verlassen des Feldes).
- Im Ad-hoc-Modus werden bestehende Datensätze nach `change` des Feldes als kompletter Datensatz validiert und gespeichert; die Zeile bleibt nach dem Reload aktiv.
- Der zeilenweise „Speichern“-Button wird im Ad-hoc-Modus durch einen Status „Ad hoc“ ersetzt; die `*`-Neuzeile bleibt bewusst explizit über „Datensatz anlegen“ steuerbar.
- Pro DataForm kann die Speichern-Erfolgsmeldung ein- oder ausgeschaltet werden. Fehler- und Validierungsmeldungen bleiben immer sichtbar.
- Die Einstellungen werden direkt in `dataforms.table_save_mode` und `dataforms.show_save_success` persistiert und dadurch automatisch in `.dfpkg`-Export/Import übernommen.
- Bestehende Projekte werden beim Öffnen von Designer/Datensätzen automatisch schema-kompatibel erweitert; neue Projekte erhalten die Spalten bereits im Installationsschema.



## RC1.8-FC1-HF65 – Speichern-Erfolg als Dialogfenster
- Nicht-modalen HF63-Toast durch ein modales natives JavaScript-`dialog` ersetzt.
- Erfolgsdialog besitzt klare Statusanzeige und explizite `OK`-Bestaetigung; kein automatisches Ausblenden mehr.
- DataForm-Verhaltenstext auf „JavaScript-Dialogfenster nach dem Speichern anzeigen“ praezisiert.
- Validierungs- und Fehlermeldungen bleiben unveraendert dauerhaft sichtbar.

## RC1.8-FC1-HF66 – Speichern-Button auch im Ad-hoc-Modus
- Der zeilenweise `Speichern`-Button wird in der tabellarischen DataForm-Ansicht nicht mehr im Ad-hoc-Modus ausgeblendet.
- Ad-hoc-Autosave bleibt aktiv; explizites Speichern ist parallel jederzeit moeglich.
- Der `Ad hoc`-Status bleibt als zusaetzliche Kennzeichnung sichtbar.


## HF68
- Vorhandenes `easyit-epManager-logo.png` zentral in Enterprise-Header und DataForm-Runtime eingebunden.
- DataForm-Logo linksbündig ausgerichtet.
- Sichere Fallback-Darstellung, falls das Enterprise-Manager-PNG fehlt.

## RC1.8-FC1-HF70 – Projekt-CRUD im Enterprise Manager
- Projektliste mit vollständigem CRUD ergänzt: Create, Read/Öffnen, Update/Bearbeiten und Delete/Entfernen.
- Projektbearbeitung für Name, Slug, Status und Beschreibung ergänzt.
- Sichere Löschbestätigung per exakter Projekteingabe; die physische Projektdatenbank wird nicht gelöscht.
- Audit- und Event-Erfassung für Projekt-Update und Projekt-Delete ergänzt.

## RC1.8-FC1-HF71 – optionale Projektdatenbank-Löschung
- Projekt-Löschdialog um die standardmäßig deaktivierte Option „Projektdatenbank ebenfalls endgültig löschen“ erweitert.
- Ohne Auswahl wird weiterhin nur die Enterprise-Projektregistrierung entfernt; die Datenbank bleibt erhalten.
- Bei aktivierter Option wird die Projektdatenbank vor Entfernen der Registrierung per `DROP DATABASE` gelöscht und anschließend verifiziert.
- System-/Enterprise-Datenbanken und Datenbanken, die noch von einem anderen Projekt referenziert werden, sind explizit geschützt.
- Scheitert die Datenbanklöschung, wird die Projektregistrierung nicht entfernt.
- Audit und Domain-Event unterscheiden `database_delete_requested`, `database_deleted` und `database_already_missing`.


## RC1.8-FC1-HF76-P4 – DataForm Transport, Pakete und API

- CSV-Import/Export nutzt die zentrale HF76-Feldtyp-Normalisierung für alle 33 Typen.
- CSV-Import arbeitet über `DataFormRecordStore` und unterstützt damit generische sowie physisch gebundene DataForms.
- Datei-/Bildwerte werden im CSV-Export portabel mit `data_base64` ausgegeben und beim Import über die Feld-Speicherstrategie neu gespeichert.
- REST-Laufzeit nutzt `DataFormRecordStore`, native JSON-Typen und sichere Media-Base64-Eingaben.
- REST unterstützt OpenAPI-taugliches PATH_INFO-Routing bei Erhalt der bisherigen Query-Aufrufe.
- OpenAPI 3.0 beschreibt Endpunktfelder, Request-/Response-Schemas, Listen, Details, Create/Update/Delete und Media-Metadaten.
- `.dfpkg` wurde auf Format 1.2 erweitert: Medien werden dedupliziert nach SHA-256 im Paket gespeichert, geprüft und im Zielprojekt neu abgelegt.
- MySQL/MariaDB-, SQLite-, Oracle- und CSV-Typ-Mappings für alle 33 Feldtypen regressionsgeprüft.

## RC1.8-FC1-HF76-P3 – DataForm Datei-/Bild-Lifecycle
- `file` und `image` vollständig für Datenbank- und Filesystem-Speicherung gehärtet.
- SHA-256-/Größenintegrität, atomische Speicherung, sichere Dateinamen und geschützter Upload-Baum ergänzt.
- Bildvorschau auf sichere Rasterformate begrenzt; SVG und aktive Bildformate werden für Bildfelder abgewiesen.
- Kontrollierten Media-Endpunkt um Download-/Inline-Entscheidung und Security-Header erweitert.
- Medien-Ersetzen, Entfernen, Datensatzlöschung, Bulk-Löschung und Fehler-Rollback abgesichert.

## RC1.8-FC1-HF76 – DataForm Field Types Final
- Abschluss der phasenweisen DataForm-Feldtypen-Erweiterung auf 33 registrierte Typen.
- CRUD, Validierung, Tabellenansicht und physische Tabellenbindung für die erweiterten Typen konsolidiert.
- `image` und `file` unterstützen verwaltete Datenbank- und Filesystem-Speicherung inklusive sicherer Vorschau, Download, Ersetzen und Löschen.
- CSV, REST/OpenAPI und `.dfpkg` verwenden dieselbe zentrale Transport-/Normalisierungsschicht.
- Portable Paketmedien werden nach SHA-256 dedupliziert und beim Import gemäß Zielprojekt neu gespeichert.
- Regenerierbare Framework-Caches mit alten absoluten Buildpfaden werden nicht mit dem finalen Paket ausgeliefert.

## RC1.8-FC1-HF76-PUBLISH11 – Kindformulare in der Realvorschau
- Die Realvorschau zeigt zu einem ausgewählten Eltern-Datensatz jetzt alle aktiven 1:n-Kind-DataForms als echte eingebettete Runtime-Oberflächen.
- Ein Wechsel des aktuellen Eltern-Datensatzes aktualisiert sämtliche Kindformulare ohne Reload der übergeordneten Vorschau.
- Kind-DataForms werden anhand des konfigurierten `lookup_field_id` auf den gewählten Eltern-Datensatz gefiltert.
- Mehrere Kindbeziehungen werden als getrennte Kindformularbereiche dargestellt; verschachtelte 1:n-Vorschauen sind bis zu einer sicheren Tiefe von drei Ebenen möglich.
- Alle eingebetteten Kindformulare bleiben im schreibgeschützten Preview-Modus; Navigation, Suche, Paginierung und Datensatzzeiger bleiben testbar.
- Parent-/Relation-Kontext bleibt bei GET-Navigation innerhalb eines Kindformulars erhalten; iframe-Höhen werden dynamisch an den Inhalt angepasst.

## RC1.8-FC1-HF76 PUBLISH14

- Realvorschau übernimmt die gespeicherte DataForm-Standardansicht explizit.
- Standardansicht table/form/dialog wird im Editor live in den Preview-iframe synchronisiert.
- Preview-Subnavigation kann die konfigurierte Ansicht nicht mehr auf Tabelle überschreiben.

## RC1.8-FC1-HF76 PUBLISH16
- 1:n-Kindformulare zeigen die gekoppelte Eltern-Eigenschaft sichtbar vorausgewählt.
- Elternkontext sperrt das FK-/Lookup-Feld und erzwingt die Eltern-ID serverseitig auch bei Bearbeitung.
- Kindansichten werden im Elternkontext vollständig auf die zugehörigen Kinddatensätze gefiltert.
- Dieselbe Semantik gilt im exportierten HTML5-Anwenderpaket.

## HF76-PUBLISH17 – DataFormActionContext 1.0
- JavaScript-Events erhalten das benannte Objekt `dataformContext`.
- `Nach Speichern` kann `afterSave(dataformContext);` aufrufen.
- After-Save-Snapshot enthaelt gespeicherte Werte, Originalwerte, Aenderungen, Feldstatus, UI- und Elternkontext.
- Enterprise- und exportierte HTML5-Runtime verwenden denselben Vertrag.


## RC1.8-FC1-HF76 PUBLISH18
- Gebundene 1:n-*‑Neuzeilen übernehmen die aktuelle Parent-ID direkt im echten Tabellenrenderer.
- Read-only-Schalter für das gebundene Fremdschlüsselfeld im Beziehungsdesigner ergänzt; Default `Ja`.
- Parent-FK wird nur bei read-only serverseitig erzwungen; bei editierbarer Bindung bleibt er Initialwert.
- Tabellenpaginierung direkt nach gespeicherten Datensätzen und vor der *‑Neuzeile positioniert.
- Exportierte HTML5-Runtime entsprechend synchronisiert.

## RC1.8-FC1-HF76 PUBLISH19
- DataForm-Eigenschaft **Volltextsuche** als expliziter Ja/Nein-Schalter umgesetzt; bestehendes `show_search` bleibt der kompatible Persistenzschlüssel.
- Neue DataForm-Eigenschaft **Filter** (`show_filter`) als unabhängiger Ja/Nein-Schalter ergänzt.
- Feldfilter und gespeicherte Filter werden bei `Filter = Nein` nicht nur ausgeblendet, sondern auch serverseitig nicht angewendet.
- Volltext-Queryparameter werden bei `Volltextsuche = Nein` ignoriert.
- Alle vier Kombinationen aus Volltextsuche/Filter sind zulässig.
- Exportierte Anwender-Runtime und `dataformContext` übernehmen beide Eigenschaften.
- Projektmigration `007_dataform_search_filter_options.php` ergänzt.

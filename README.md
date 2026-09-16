# easyIT Enterprise / DataForm5 – servergebundene Lizenz- und Runtime-Architektur

Stand: 2026-09-16  
Version: `DataForm5-License-Runtime-0.1.0-2026-09-16`

Dieses Paket setzt den ausführbaren Kern der Phasen 1–12 um: Der auslieferbare `enterpriseManager` enthält nur Client/Shell/DB-Adapter. Die funktionskritische DataForm-Logik verbleibt im getrennten `easyit-license-server` und wird nach erfolgreicher Lizenz-/Installationsprüfung für konkrete Runtime- und Action-Entscheidungen verwendet.

## Schutzgrenze

**Nur Server:** `DataFormCompiler`, `DefinitionValidator`, `ActionResolver`, Designer-Revisionierung, Lizenz-/Installationsprüfung, Server-Signierung.  
**Lokal:** `LicenseApiClient`, `DataFormRuntimeClient`, `DataFormDesignerClient`, `EnterpriseRuntimeBridge`, Installationsidentität und Runtime-Cache.

Neue DataForms beziehungsweise neue veröffentlichte Revisionen können ohne den geschützten Server-Core nicht erzeugt werden. `create`, `update`, `delete` und Relation-Schreibaktionen benötigen eine serverseitige Action-Entscheidung. Kundendatensätze selbst werden dabei nicht an den Lizenzserver übertragen.

## Enthalten

- Ed25519-Serveridentität und Ed25519-Installationsidentitäten
- Aktivierung mit Installationslimit
- Module: `dataform`, `designer`, `export`, `api`, `themes`
- `runtime/start`, `runtime/renew`, `runtime/action`, `runtime/action/result`, `runtime/end`
- signierte Runtime-Manifeste und Action-Decisions
- Nonce-/Replay-Schutz und Lizenz-/Installationsgenerationen
- signierter lokaler Lease-/Grace-Cache einschließlich Clock-Rollback-Prüfung
- Designer-API `validate`, `compile`, `publish`, `revisions`
- versionierte DataForm-Revisionen
- Wartungsmodus `off|read_only|full`
- produktive Lizenzverwaltung per Server-CLI
- STAND-4-kompatibler Patch-Installer für `products/dataform/records.php`
- Release-/Security-Gates gegen versehentlich ausgelieferten Server-Core/private Server-Keys

## Verzeichnisse

- `easyit-license-server/` – **nicht an Kunden ausliefern**
- `enterprise-integration/` – auslieferbarer Client plus Patch-/Aktivierungswerkzeuge
- `gates/` – Syntax-/Runtime-/Security-Gates
- `docs/` – API, Installation, Umsetzungsstatus

## Schnellstart unter XAMPP

1. `easyit-license-server/config/app.example.php` nach `config/app.php` kopieren und DB-Zugang setzen.
2. `php easyit-license-server/bin/generate-server-key.php`
3. `php easyit-license-server/bin/migrate.php`
4. Produktive Lizenz anlegen, z. B.:

   `php easyit-license-server/bin/admin.php customer:create --name="Muster GmbH"`

   danach mit der ausgegebenen Customer-ID:

   `php easyit-license-server/bin/admin.php license:create --customer=CUS-... --modules=dataform,designer --max-installations=3`

5. Webroot des Lizenzservers auf `easyit-license-server/public/` setzen.
6. Integration anwenden:

   `php enterprise-integration/tools/apply_to_enterpriseManager.php --root=D:\xampp\htdocs\enterpriseManager`

7. In `enterpriseManager/config/licensing.php` `server_url` und den Public Key unter `trusted_server_keys` eintragen.
8. Installation aktivieren:

   `php D:\xampp\htdocs\enterpriseManager\tools\activate_license.php --root=D:\xampp\htdocs\enterpriseManager --license=LIC-....<secret>`

9. Vorhandene/neu erzeugte Definition veröffentlichen:

   `php D:\xampp\htdocs\enterpriseManager\tools\publish_dataform_definition.php --root=D:\xampp\htdocs\enterpriseManager --project=1 --dataform=1 --definition=D:\definition.json`

10. Gate:

   `php gates/DATAFORM_LICENSE_SECURITY_GATE.php --enterprise=D:\xampp\htdocs\enterpriseManager --server=D:\xampp\htdocs\easyit-license-server`

## Wichtig zur aktuellen Integration

Der Patch-Installer ist gezielt auf den nachgewiesenen STAND-4-`records.php`-/`DataFormRecordSet`-Aufbau ausgelegt. Das aktuell vollständige spätere `enterpriseManager`-Basis-ZIP stand beim Erzeugen dieses Pakets nicht als entpackbares Basisartefakt zur Verfügung. Deshalb wird kein erfundener Komplettstand überschrieben. Client, Server, Designer-API und Gates sind vollständig im Paket; die bestehende Designer- und Superadmin-Oberfläche wird erst nach Einspielen in den konkreten aktuellen Projektbaum an deren reale Routen/Seiten gebunden.

## Sicherheitsgrenze

Der Schutz betrifft die DataForm-Programmlogik und Lizenznutzung. Ein Administrator der Kundendatenbank kann seine eigenen Daten selbstverständlich außerhalb von DataForm direkt verändern; eine Lizenzarchitektur kann und soll das nicht verhindern.

# Umsetzungsstatus

## Umgesetzt

- separater, nicht auslieferbarer Lizenz-/DataForm-Server
- MariaDB/MySQL-/PostgreSQL-fähige PDO-Schicht (Schema verwendet portable IDs/Integer-Timestamps)
- Ed25519 Server-Schlüssel
- Ed25519 Installationsidentität; privater Installationsschlüssel bleibt lokal
- signierte Serverantworten mit Trust-Store-Pinning
- Aktivierung und Installationslimit
- Module (`dataform`, `designer`, `export`, `api`, `themes`)
- `runtime/start`
- `runtime/renew`
- serverseitiger `DataFormCompiler`
- signiertes Runtime-Manifest
- `runtime/action`
- serverseitiger `ActionResolver`
- `runtime/action/result`
- Nonce-/Replay-Schutz
- Lizenz-/Installationsgenerationen
- Lease/Grace-Daten im Manifest
- signierter lokaler Runtime-Cache mit Lease-/Grace-Fallback und Clock-Rollback-Prüfung
- lokaler Runtime-Bridge-Patch für den STAND-4-`records.php`-Aufbau
- Action-Vorautorisierung für `save_record`, `create`, `update`, `delete_record`, `delete`, `bulk_delete`
- Abschlussmeldung an den Server nach den bekannten STAND-4-Erfolgsmarkern
- Release-Gate: geschützter Core darf nicht im Kundenpaket vorkommen
- Gate gegen Server-Private-Key im Enterprise-Paket
- Designer-API: `validate`, `compile`, `publish`, `revisions`
- geschützte serverseitige Feld-/Event-/Relationsvalidierung
- unveränderliche DataForm-Revisionen mit `compiled`/`published`/`retired`

## Bewusst getrennt / noch nicht automatisch in einen unbekannten Basisstand eingepatcht

Da das aktuelle vollständige `enterpriseManager`-ZIP in dieser Sitzung nicht als entpackbares Basisartefakt verfügbar war, wird die Integration als idempotentes Drop-in-/Patch-Paket geliefert. Der Patch-Installer ist auf die nachgewiesene STAND-4-Struktur (`products/dataform/records.php`, `DataFormRecordSet`) ausgelegt.

Die Designer-API ist serverseitig umgesetzt; wegen des fehlenden vollständigen aktuellen `enterpriseManager`-Basisarchivs wird die bestehende Designer-Oberfläche jedoch nicht automatisch umverdrahtet. Der mitgelieferte `DataFormDesignerClient` stellt die Integrationsschnittstelle bereit. Die vorhandene Superadmin-UI für Kunden/Lizenzen kann aus demselben Grund nicht verlustfrei in einen unbekannten Basisstand eingepatcht werden; die zentrale Lizenzdatenbank und Services sind dafür vorbereitet.

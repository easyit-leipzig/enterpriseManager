# Phase Y – Replikation und Cluster-Synchronisierung

Phase Y führt eine kontrollierte Event-Replikation über den in Phase X
eingeführten Storage-Layer ein.

## Architektur

- `ReplicationEvent`: unveränderliches Ereignis mit SHA-256-Integritätsprüfung
- `ReplicationTransportInterface`: Transportabstraktion
- `SharedStorageReplicationTransport`: gemeinsamer Dateispeicher als Transport
- `ReplicationStateStore`: merkt den letzten verarbeiteten Streamstand
- `ClusterSynchronizer`: publish/consume und Handler
- `ReplicationSnapshotService`: reproduzierbare Metadaten-Snapshots

## Konfiguration

```text
REPLICATION_ENABLED=false
REPLICATION_DRIVER=shared-storage
REPLICATION_CHANNEL=enterprise
REPLICATION_STORAGE_DISK=shared
REPLICATION_BASE_PATH=replication
CLUSTER_NODE_ID=node-a
```

## CLI

```bash
php tools/replication-status.php
php tools/replication-publish-modules.php
php tools/replication-consume.php 100
```

## Sicherheitsgrenze

Phase Y repliziert absichtlich keine beliebigen Dateien und insbesondere keinen
ausführbaren PHP-Code automatisch. Das Replikationssystem überträgt nur explizit
erzeugte Ereignisse und Metadaten. Jedes Event enthält eine SHA-256-Prüfsumme.

Damit bleibt ein kompromittierter Shared-Storage-Bereich von einer automatischen
Codeinstallation getrennt. Modul-Pakete werden weiterhin über den bestehenden
validierten Paketinstaller installiert.

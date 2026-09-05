# Phase V – Cluster Foundation

Phase V führt eine lokal testbare Cluster-Grundlage ein, ohne externe Infrastruktur
wie Redis, NFS oder S3 vorauszusetzen.

## Enthalten

- Node-Registry auf Dateibasis
- Node-Heartbeat
- deterministische Leader-Ermittlung (kleinste Node-ID unter online Nodes)
- Cluster-Health-Snapshot
- Cluster-Adminseite
- JSON-Statusendpoint
- CLI für Heartbeat und Status

## Konfiguration

In `.env` können u. a. gesetzt werden:

```text
CLUSTER_ENABLED=false
CLUSTER_NODE_ID=node-a
CLUSTER_NODE_NAME=Node A
CLUSTER_HEARTBEAT_TTL=90
CLUSTER_REGISTRY_PATH=storage/framework/cluster/nodes.json
CLUSTER_SHARED_SECRET=
```

## Betrieb

Standalone:

```bash
php tools/cluster-heartbeat.php
php tools/cluster-status.php
```

Mehrere Server können dieselbe Registry nur dann sinnvoll nutzen, wenn
`CLUSTER_REGISTRY_PATH` auf ein gemeinsam verfügbares und ausreichend
konsistentes Dateisystem zeigt. Für echte Hochverfügbarkeit ist in einer späteren
Phase ein DB-/Redis-basierter Registry- und Lock-Provider vorzuziehen.

## Sicherheitsprinzip

Phase V implementiert bewusst noch keine Netzwerkaufnahme fremder Nodes.
Dadurch wird keine ungesicherte Cluster-API geöffnet. Node-to-node Authentifizierung
und verteilte Transportprovider gehören in eine spätere Härtungsphase.

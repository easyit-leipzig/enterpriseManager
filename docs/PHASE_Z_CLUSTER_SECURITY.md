# Phase Z – Cluster-Sicherheit und Node-Authentifizierung

Phase Z ergänzt die Cluster- und Replikationsschicht um Authentizität und
Replay-Schutz.

## Verfahren

- HMAC-SHA-256 pro Node
- eigener `CLUSTER_NODE_SECRET` je Node
- Trust Store mit vertrauenswürdigen Node-Secrets
- Nonce je Nachricht
- Timestamp-Prüfung
- Replay-Store
- Fingerprints in der Admin-Oberfläche, niemals Klartext-Secrets

## Konfiguration

```text
CLUSTER_SECURITY_ENABLED=true
CLUSTER_NODE_ID=node-a
CLUSTER_NODE_SECRET=<langes-zufälliges-secret>
CLUSTER_SIGNATURE_TTL=120
CLUSTER_TRUST_STORE_PATH=storage/framework/cluster/trusted-nodes.json
CLUSTER_REPLAY_STORE_PATH=storage/framework/cluster/replay.json
```

Auf Node B wird das Secret von Node A im Trust Store hinterlegt und umgekehrt.

CLI:

```bash
php tools/cluster-trust-node.php node-a "<secret>" "Produktionsnode A"
php tools/cluster-heartbeat.php
php tools/replication-publish-modules.php
```

## Schutz

Signaturen binden Payload, Node-ID, Timestamp und Nonce. Empfangene Nachrichten
werden nur akzeptiert, wenn der Node vertraut ist, die HMAC-Signatur stimmt und
Nonce/Timestamp nicht bereits verbraucht bzw. abgelaufen sind.

## Grenzen

HMAC benötigt einen sicheren Secret-Austausch außerhalb der Plattform. Phase Z
öffnet weiterhin keine Node-to-node-HTTP-Schnittstelle. Für größere Cluster kann
später auf asymmetrische Schlüssel/Zertifikate erweitert werden.

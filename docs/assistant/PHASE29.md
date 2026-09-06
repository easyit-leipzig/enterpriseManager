# Assistant Phase 29 – PRIMARY-Failover, Heartbeat, Lease und Quorum-Schutz

Phase 29 ergänzt `dataform.trust-failover` und baut auf der Phase-28-Federation auf.

## Ziel

Ein `PRIMARY` darf nach Aktivierung von Phase 29 nicht allein aufgrund seiner lokalen Rolle Releases signieren. Die Signierberechtigung ist zusätzlich an ein nicht abgelaufenes, kryptographisch geprüftes und durch Mehrheitsquorum bestätigtes Leadership-Lease gebunden.

Damit werden zwei Situationen getrennt:

- **Heartbeat stale**: PRIMARY ist verdächtig, aber ein Failover ist noch nicht erlaubt.
- **Lease expired + grace**: PRIMARY besitzt keine gültige Führungsberechtigung mehr; eine neue Election-Epoche darf beginnen.

## Cluster und Quorum

Phase 29 verwendet eine explizite, vom PRIMARY mit dem Phase-25/26-Release-Key signierte Cluster-Mitgliedschaft. Jedes Mitglied besitzt zusätzlich eine eigene Ed25519-Failover-Identity. Diese Identity ist von den Release-Signing-Keys getrennt und wird für Heartbeats, Lease-ACKs und Election-Votes verwendet.

Ein Failover-Cluster benötigt mindestens drei stimmberechtigte Mitglieder. Das Quorum ist immer die absolute Mehrheit:

`quorum = floor(votingMembers / 2) + 1`

Beispiel: 3 Mitglieder -> Quorum 2; 5 Mitglieder -> Quorum 3.

## Leadership-Lease

Ein PRIMARY erzeugt ein signiertes Lease-Proposal. Die stimmberechtigten Peers prüfen das Proposal und signieren ACKs. Erst wenn die Mehrheit exakt dasselbe Proposal bestätigt, wird ein Leadership-Lease aktiviert.

Eine Verlängerung ist kein rein lokaler Vorgang. Auch jede Renewal-Sequenz benötigt erneut Quorum-ACKs. Dadurch kann ein isolierter alter PRIMARY sein Lease nicht unbegrenzt lokal verlängern.

`ReleaseTrustStore` ruft nach Phase-29-Aktivierung `TrustFailoverLeaseGuard` auf. Geprüft werden unter anderem:

- lokale Rolle ist `PRIMARY`
- Lease-Holder entspricht der lokalen Instanz
- Lease ist noch nicht abgelaufen
- Cluster-Digest stimmt
- Holder-Signatur stimmt
- Proofs stammen von eindeutigen Voting-Mitgliedern
- Proofs gehören zur richtigen Epoche und Lease-Sequenz bzw. Election
- Proof-Anzahl erreicht das konfigurierte Quorum

## Heartbeat

Heartbeats werden vom aktuellen PRIMARY mit dessen Failover-Identity signiert und enthalten unter anderem:

- PRIMARY-Instanz-ID
- aktuelle Epoche
- Digest des Leadership-Lease
- Lease-Ablaufzeit
- Emissionszeit

Ein abgelaufener Heartbeat führt zunächst zu `SUSPECTED`. Solange das Leadership-Lease einschließlich Sicherheitsfrist noch gültig ist, darf kein SECONDARY eine Wahl starten.

## Election und Split-Brain-Schutz

Nach sicherem Lease-Ablauf kann ein SECONDARY eine neue Election-Epoche beginnen. Jedes Voting-Mitglied darf pro Epoche nur **einen** Kandidaten bestätigen. Der persistente Vote-State verhindert eine zweite konkurrierende Stimme.

Hat ein Peer bereits für Epoche 2 abgestimmt, verweigert er außerdem eine Lease-Erneuerung eines alten PRIMARY in Epoche 1.

Die Promotion erfordert:

- altes Lease ist abgelaufen plus Grace
- Kandidat ist ein Voting-Member und aktuell SECONDARY
- identische Election-Epoche und Election-Request-Digests
- eindeutige gültige Ed25519-Stimmen in Mehrheitszahl
- aktiver privater Release-Signing-Key ist auf dem Kandidaten vorhanden
- exakte administrative Bestätigung

Die erfolgreiche Promotion erzeugt sofort ein quorumgesichertes Election-Lease. Erst danach erlaubt der zentrale Release-Signing-Guard Signaturen.

## Persistenz

Laufzeitdaten liegen unter:

`storage/assistant/trust/instances/failover/`

Dazu gehören lokal unter anderem:

- `identity.json`
- `private/<instance>.key`
- `cluster.json`
- `leadership-lease.json`
- `observed-leadership-lease.json`
- `last-heartbeat.json`
- `votes-cast.json`
- `failover-audit.jsonl`
- `outbox/*.json`

Diese Laufzeitdateien werden **nicht** im kumulativen Overlay ausgeliefert.

## Audit

Identity-Erzeugung, Clusterkonfiguration/-import, Lease-Proposals, ACKs, Aktivierungen, Heartbeats, Election-Requests, Votes und Promotions werden in einer SHA-256-hashverketteten Failover-Auditdatei protokolliert.

## Kompatibilität

Solange `cluster.json` nicht existiert bzw. Phase 29 nicht aktiviert wurde, bleibt das Signing-Verhalten aus Phase 1–28 unverändert. Erst ein explizit konfigurierter Failover-Cluster aktiviert den zusätzlichen Lease-Fence.

## Voraussetzungen

- PHP `ext-sodium`
- Phase-28-Instanzinitialisierung
- vor Cluster-Aktivierung synchronisierte/vertrauenswürdige Release-Trust-Anker auf allen Teilnehmern
- für eine spätere PRIMARY-Promotion muss der Kandidat den privaten Release-Signing-Key über Phase 27/28 sicher wiederhergestellt haben

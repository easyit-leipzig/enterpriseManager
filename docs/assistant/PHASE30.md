# Assistant Phase 30 – sichere Cluster-Mitgliedschaft und Quorum-Reconfiguration

Phase 30 schließt die mit Phase 29 eingeführte Failover-Clustersteuerung um sichere Änderungen der Mitgliedschaft ab.

## Neuer Assistent

`dataform.trust-membership`

## Ziele

- neue SECONDARY-/VERIFY-Knoten kontrolliert aufnehmen,
- vorhandene Knoten entfernen,
- Voting / Non-Voting ändern,
- Quorum neu berechnen,
- Membership-Epochen versionieren,
- Split-Brain während einer Reconfiguration verhindern.

## Joint Consensus

Eine Änderung wird nicht direkt auf `cluster.json` geschrieben. Der Ablauf ist:

1. PRIMARY erzeugt einen signierten Membership-Vorschlag.
2. PREPARE benötigt gleichzeitig das Mehrheitsquorum der alten und der neuen Voting-Menge.
3. Danach wird `membership/joint.json` aktiv.
4. Während dieses Zustands sind Release-Signing, Lease-Erzeugung, Heartbeats und Elections gefenced.
5. COMMIT benötigt erneut das Mehrheitsquorum der alten und der neuen Voting-Menge.
6. Erst danach wird die bereits vorab signierte finale Clusterkonfiguration atomar aktiviert.
7. Alte Leadership-Leases werden entfernt, da sie an den alten Membership-Digest gebunden waren.

## Membership-Epochen

Bestehende Phase-29-Cluster ohne explizite Epoche werden als Epoche 1 behandelt. Jede erfolgreiche Reconfiguration erhöht die Membership-Epoche um 1.

## Voting-Regeln

- Zielkonfiguration: mindestens 3 Voting-Mitglieder.
- Quorum: `floor(voters / 2) + 1`.
- Das aktive PRIMARY darf in derselben Änderung nicht entfernt oder auf Non-Voting gesetzt werden. Zuerst Leadership übertragen.
- Neue Knoten dürfen als Voting oder Non-Voting aufgenommen werden.
- Entfernte lokale Instanzen werden beim Import der finalen Konfiguration auf `VERIFY_ONLY / RETIRED` gesetzt.

## Signatur- und Fence-Regeln

Der finale Cluster wird vor Aktivierung des Joint-Consensus mit dem vertrauenswürdigen Release-Key signiert. Während Joint Consensus blockiert `TrustFailoverLeaseGuard` Release-Signaturen. `TrustFailoverService` blockiert außerdem neue Leadership-Leases, Heartbeats und Elections.

## Persistenz

Laufzeitdaten liegen unter:

`storage/assistant/trust/instances/failover/membership/`

Dazu gehören `joint.json`, `history/`, `outbox/` und `membership-audit.jsonl`.

## Testfälle Phase 30

Der Smoke-Test prüft unter anderem:

- P/A/B Ausgangscluster, Quorum 2,
- Aufnahme eines neuen Knotens C,
- C wird Voting,
- B wird Non-Voting,
- altes Quorum ohne neues Quorum wird blockiert,
- Dual-Quorum aktiviert Joint Consensus,
- Signing- und Leadership-Fence im Joint-Zustand,
- finale Membership-Epoche 2,
- neues Leadership-Lease nach Reconfiguration,
- zweite Reconfiguration entfernt B vollständig,
- B wird `VERIFY_ONLY / RETIRED`,
- finale Membership-Epoche 3,
- hashverkettetes Membership-Audit.

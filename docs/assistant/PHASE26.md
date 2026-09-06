# Assistant Phase 26 – Trust-Policy und Schlüsselverwaltung

Phase 26 erweitert die Ed25519-Vertrauenskette aus Phase 25 um eine operative Trust-Policy.

## Assistent

`dataform.trust-policy`

## Vertrauensstufen

- `RELEASE`: Schlüssel darf Releases signieren und signierte Releases für Installation/Update autorisieren.
- `VERIFY_ONLY`: Schlüssel darf nur für historische Verifikation verwendet werden; Installation/Update wird blockiert.
- `NONE`: keine Vertrauenswirkung.

## Schlüsselstatus

- `TRUSTED`: Policy kann den Schlüssel verwenden, sofern Gültigkeit und Trust-Level passen.
- `SUSPENDED`: temporär gesperrt; Installation/Update wird sofort blockiert. Reaktivierung ist nach exakter Bestätigung möglich.
- `REVOKED`: dauerhaft widerrufen. Ein Widerruf ist irreversibel und setzt den Trust-Level auf `NONE`.

Zusätzlich werden `validFrom` und optional `validUntil` ausgewertet. Noch nicht gültige oder abgelaufene Schlüssel werden für Installation, Update, Migration und Signierung blockiert.

## Zentrale Wirkung

Die Phase-26-Policy wird in `ReleaseCatalogService::verifyLibraryRelease()` geprüft. Da `DataFormModuleLibraryService::stage()` bereits zentral auf diese Verifikation zugreift, gilt eine Sperrung, ein Widerruf oder ein Ablauf automatisch für:

- direkte Modulinstallation,
- Modulabhängigkeiten,
- Phase-20-Installationspläne,
- Phase-21-Updates,
- Phase-22-Migrationen.

## Audit

Jede Schlüsseloperation erzeugt ein Event unter:

`storage/assistant/trust/audit.jsonl`

Die Events sind über `previousHash` und `eventHash` SHA-256-verkettet. Eine nachträgliche Änderung eines alten Audit-Eintrags wird durch `verifyTrustAudit()` erkannt.

Protokolliert werden insbesondere:

- Schlüsselerzeugung,
- Rotation,
- Sperrung,
- Reaktivierung,
- Widerruf,
- Änderung der Vertrauensstufe,
- Änderung der Gültigkeit.

## Rotation und Revocation

Eine reguläre Rotation aus Phase 25 bleibt vollständig unterstützt. Wird jedoch ein Vorgängerschlüssel später als kompromittiert widerrufen, wird die davon abhängige Trust-Chain bewusst ungültig. Damit kann ein kompromittierter Chain-Anker nicht weiter als Vertrauensursprung dienen.

## Private Schlüssel

Private Ed25519-Schlüssel bleiben ausschließlich Laufzeitdaten unter:

`storage/assistant/trust/private/`

Sie sind nicht Bestandteil des Overlays und werden nicht über öffentliche Trust-/Katalogexporte ausgegeben.

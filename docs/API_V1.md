# easyIT License/DataForm API v1

Öffentlich:

- `GET /api/v1/health`
- `POST /api/v1/activation/request`

Signierte Installationsrequests:

- `POST /api/v1/runtime/start`
- `POST /api/v1/runtime/renew`
- `POST /api/v1/runtime/action`
- `POST /api/v1/runtime/action/result`
- `POST /api/v1/runtime/end`

Signatur des Installationsrequests (Ed25519):

```text
METHOD
PATH
REQUEST_ID
INSTALLATION_ID
TIMESTAMP
NONCE
SHA256(BODY)
```

Erforderliche Header:

```text
X-EasyIT-Request-ID
X-EasyIT-Installation
X-EasyIT-Timestamp
X-EasyIT-Nonce
X-EasyIT-Key-ID
X-EasyIT-Signature
```

Jede Serverantwort enthält:

```text
X-EasyIT-Server-Key-ID
X-EasyIT-Server-Signature
```

Die Serversignatur gilt über die exakten Bytes des JSON-Response-Bodys.


## Designer

- `POST /api/v1/designer/validate` – Draft serverseitig prüfen
- `POST /api/v1/designer/compile` – validierte, versionierte Serverrevision erzeugen
- `POST /api/v1/designer/publish` – Revision veröffentlichen; bisherige Published-Revision wird retired
- `POST /api/v1/designer/revisions` – Revisionsliste für Projekt/DataForm

Alle Designer-Endpunkte verlangen eine gültige Installationssignatur sowie die Module `dataform` und `designer`. Die geschützte Validierungs-/Compilerlogik bleibt ausschließlich im Lizenz-/DataForm-Server.

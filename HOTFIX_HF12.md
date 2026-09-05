# RC1.8-FC1-HF12 – Administration & Setup Recovery

## Behoben
- Enterprise-Dashboard bietet dauerhaft **Installation / Setup** und **Datenbank-Assistent**.
- Fehlende Admin-DB-Konfiguration zeigt direkte Reparaturaktionen statt nur einer Fehlermeldung.
- Authentifizierte Hauptnavigation enthält Setup und DB-Assistent.
- Installer 2.0 bleibt nach gesetztem Install-Lock als Wartungs-/Reparatureinstieg verwendbar.
- Datenbank-Assistent kann eine fehlende `DataForm5-Core/.env` kontrolliert aus `.env.example` neu erzeugen.
- Bestehende Migrationen werden weiterhin per Checksum erkannt und mit `SKIP ... Checksum OK` übersprungen.
- Dashboard unterscheidet **Neues Projekt anlegen** und **Vorhandenes Projekt registrieren**.

## Sollablauf bei fehlender Konfiguration
Dashboard → Installation / Setup oder Datenbank-Assistent → Verbindung testen → DBs prüfen/anlegen → Schemas prüfen/migrieren → `.env` aktualisieren → Dashboard.

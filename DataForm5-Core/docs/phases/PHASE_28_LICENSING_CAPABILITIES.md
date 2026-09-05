# Phase 28 – Lizenz-, Capability- und Berechtigungsfreigabe-Layer

Diese Phase ergänzt den Core um eine produktneutrale Lizenz- und Capability-Prüfung. Der Core entscheidet nicht über Verkauf oder Aktivierungsserver, sondern stellt stabile Verträge bereit, an die lokale Dateien, Datenbanken oder spätere Lizenzdienste angebunden werden können.

## Bestandteile

- `LicenseProviderInterface` für austauschbare Lizenzquellen
- `LicenseManagerInterface` als zentrale Laufzeit-API
- Lizenzstatus `active`, `grace`, `expired`, `disabled`, `not_yet_valid`, `community` und `missing`
- Capability- und Produktfreigaben
- Ablaufdatum und Kulanzzeitraum
- Community-Fallback für allgemeine Core-Funktionen
- Exception bei verpflichtenden, aber nicht lizenzierten Capabilities

## Sicherheitsregel

Lizenzprüfungen ersetzen niemals Rollen-, Rechte-, CSRF- oder Authentifizierungsprüfungen. Eine Capability erlaubt lediglich die technische Verfügbarkeit einer Funktion; der konkrete Benutzer benötigt zusätzlich die passende Berechtigung.

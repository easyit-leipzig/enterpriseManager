# Phase 5 – Security Layer

Build 0005 ergänzt den DataForm5-Core um eine produktunabhängige Sicherheitsbasis:

- austauschbare Session-Speicher (`native`, `array`)
- Passwort-Hashing und Rehash-Prüfung
- Benutzerprovider und Authentifizierung
- Login, Logout und Session-Regeneration
- Rollen, Berechtigungen und definierbare Gates
- CSRF-Token, Formularfeld und Prüfung
- Middleware für Authentifizierung und CSRF

Produktdatenbanken implementieren später einen eigenen `UserProviderInterface`-Adapter. Der Core enthält bewusst keine produktspezifischen Benutzertabellen.

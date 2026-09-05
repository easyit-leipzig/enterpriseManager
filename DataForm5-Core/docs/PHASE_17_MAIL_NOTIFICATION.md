# Phase 17 – Mail- und Benachrichtigungs-Layer

Build 0017 ergänzt den DataForm5-Core um eine produktneutrale Mail- und Benachrichtigungsschicht.

## Bestandteile

- `Message` und validierte `Address`-Objekte
- Mail-Treiber `log`, `native` und `null`
- zentrale Absenderkonfiguration
- PHP-Template-Renderer
- `NotificationInterface` und `NotificationManager`
- Service-Container-Integration

Produktiv kann der native PHP-Mailtransport verwendet werden. Für lokale Entwicklung ist der Log-Treiber der sichere Standard. Ein SMTP-Treiber kann später ohne Änderung der aufrufenden Produktmodule ergänzt werden, weil alle Zugriffe über `MailerInterface` und `MailManager` erfolgen.

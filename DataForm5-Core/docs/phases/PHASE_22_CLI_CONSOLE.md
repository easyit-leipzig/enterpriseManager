# Phase 22 – CLI- und Konsolen-Layer

Phase 22 ergänzt den DataForm5-Core um eine produktneutrale Kommandozeilenschnittstelle. Befehle werden zentral registriert, erhalten positionale Argumente und lange Optionen und liefern standardisierte Exit-Codes.

## Einstieg

```bash
php bin/dataform list
php bin/dataform about
php bin/dataform health --mode=readiness
```

## Exit-Codes

- `0`: erfolgreich
- `1`: Laufzeit- oder Befehlsfehler
- `2`: unbekannter Befehl oder fehlerhafte Eingabe

Eigene Produkt- oder Modulbefehle implementieren `CommandInterface` oder erweitern `AbstractCommand` und werden in `CommandRegistry` registriert.

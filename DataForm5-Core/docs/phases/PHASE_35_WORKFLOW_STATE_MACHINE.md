# Phase 35 – Workflow- und State-Machine-Layer

Build 0035 ergänzt DataForm5-Core um eine produktneutrale Workflow-Engine.

## Funktionen

- benannte Workflows mit festgelegtem Initialzustand
- explizite Zustände und Übergänge
- mehrere erlaubte Ausgangszustände je Übergang
- Guards als Callback oder Container-Service
- Aktionen als Callback oder Container-Service
- Instanzdaten und Versionszählung
- Übergangshistorie einschließlich Fehlerprotokoll
- Abfrage verfügbarer und zulässiger Übergänge
- austauschbarer Workflow-Speicher

## Sicherheits- und Konsistenzregeln

Ein Zustand darf ausschließlich über einen registrierten Übergang geändert werden. Guards werden vor Aktionen geprüft. Ein blockierter Übergang verändert weder Zustand noch Versionsnummer. Persistente Stores können später ergänzt werden, ohne die Workflow-API zu ändern.

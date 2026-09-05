# Phase 39 – OpenAPI- und API-Dokumentations-Layer

Der Layer erzeugt OpenAPI-3.1-Dokumente aus expliziten, produktneutralen Metadaten. Er beschreibt Endpunkte, Parameter, Request Bodies, Responses und wiederverwendbare Schemas. Die Definition bleibt von Controller- und Produktlogik getrennt.

## Architekturregel

OpenAPI-Metadaten dokumentieren öffentliche Verträge. Sie dürfen keine Geheimnisse, internen Dateipfade oder vertraulichen Beispieldaten enthalten.

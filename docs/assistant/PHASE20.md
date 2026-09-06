# Assistant Phase 20 – Modulabhängigkeiten und Installation

Phase 20 erweitert die Modulbibliothek aus Phase 19 um deklarierte Modul-Releases, semantische Versionsbedingungen, einen Abhängigkeitsresolver, Installationspläne und ein projektbezogenes Installationsregister.

## Assistent

`dataform.module-dependencies`

## Release-Metadaten

Jedes Modul kann eine Release-Version und Abhängigkeiten besitzen. Transportiert werden die Metadaten im Modul-ZIP unter:

`module/release.json`

Schema:

`easyit.dataform.module-release.v1`

Ältere Module ohne Release-Metadaten bleiben kompatibel und werden als Version `1.0.0` ohne Abhängigkeiten behandelt.

## Unterstützte Versionsbedingungen

- exakt, z. B. `1.4.2`
- Caret, z. B. `^1.2.0`
- Tilde, z. B. `~1.4.0`
- Vergleichsketten, z. B. `>=1.2.0 <2.0.0`
- Wildcards, z. B. `1.2.x`, `1.x`, `*`
- Alternativen über `||`

## Abhängigkeiten

Deklarationsformat im Assistenten:

`module-id|versionsbereich|required`

oder:

`module-id|versionsbereich|optional`

Pflichtabhängigkeiten führen bei fehlendem oder inkompatiblem Modul zum `FAIL`. Optionale Abhängigkeiten erzeugen eine Warnung und blockieren die Installation nicht.

## Auflösung

Die Auflösung bevorzugt für das Zielprojekt eine passende projektbezogene Modulversion und verwendet anschließend die systemweite Modulbibliothek. Bereits kompatibel installierte Module werden als erfüllt erkannt. Zyklen werden erkannt und blockiert.

Typische Planstatus:

- `INSTALL`
- `SATISFIED`
- `REINSTALL`
- `UPGRADE`
- `DOWNGRADE`

Die Installationsreihenfolge wird topologisch ermittelt. Beispiel: Wenn `feature` von `base ^1.0.0` abhängt, wird `base` vor `feature` installiert.

## Vorprüfung und Übernahme

Vor der ersten Änderung werden alle zu installierenden Module vollständig gestaged, gemappt und über die Phase-18-Prüfung vorgeprüft. Erst ein insgesamt zulässiger Plan kann übernommen werden. Die eigentliche Installation verwendet die bestehende Phase-18-/Phase-19-Pipeline und schreibt DataForm-Zustände weiterhin über den `AssistantStateStore`, womit Phase-14-Historie/Undo/Redo erhalten bleibt.

Mappings können global oder modulbezogen angegeben werden. Modulbezogene Einträge verwenden den Präfix:

`moduleId::source|target`

## Installationsregister

Installierte Module werden projektbezogen gespeichert unter:

`projects/<project-id>/config/assistant/module-installations.json`

Schema:

`easyit.assistant.module-installations.v1`

Gespeichert werden u. a. Modul-ID, Version, Herkunft, Paket-SHA, enthaltene DataForms, Abhängigkeiten sowie Installationszeitpunkt.

## Direkte Phase-19-Installation

Abhängigkeitsfreie Module dürfen aus der Phase-19-Modulbibliothek weiterhin direkt installiert werden und werden dabei registriert. Sobald ein Modul Abhängigkeiten deklariert, wird die direkte Installation blockiert und der Phase-20-Abhängigkeitsassistent verlangt.

## Backup / Restore

Das Installationsregister liegt im Projekt und wird deshalb durch Phase 10 mitgesichert. Bei Restore unter einer neuen Projekt-ID werden `projectId` und projektbezogene `sourceLocator`-Werte auf das neue Projekt retargetet.

## UI

Phase 20 ist auf `project.management`, `project.detail`, `dataform` und `assistant.center` registriert.

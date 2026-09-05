# RC1.8 Phase 5.6 – SDK-Konsole

`php easyit` ist der zentrale CLI-Einstieg.

Generatoren: make:module, make:provider, make:event, make:listener,
make:migration, make:model, make:controller, make:view, make:api, make:theme,
make:job, make:command und make:test.

`make:module` nutzt den bestehenden ModuleScaffolder. Alle übrigen Generatoren
erweitern ein vorhandenes Modul über `--module=<slug>`. Vorhandene Dateien
werden nicht überschrieben.

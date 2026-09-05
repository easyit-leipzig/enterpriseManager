# RC1.8 Phase 5.4 – Hook Inspector

Phase 5.4 ergänzt den Developer Mode um eine dedizierte Lifecycle-/Plugin-Hook-Sicht.

Angezeigt werden:

- Modul
- Hookname
- Handler
- Diagnosepriorität
- Aktivstatus
- letzter passender Runtime-Trace

Web: `app/developer/hooks.php`

CLI:
`php tools/developer-hooks.php [Suchbegriff]`

Die Ansicht ist rein diagnostisch und führt keine Hooks aus.

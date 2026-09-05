# RC1.8 Phase 5.5 – Request Profiler

Der Request Profiler sammelt bei aktivem Developer Mode Messpunkte in den
Kategorien `php`, `sql`, `cache`, `filesystem`, `http`, `api`, `modules`,
`queue` und `scheduler`.

Web: `app/developer/profiler.php`

CLI: `php tools/developer-profiler.php`

Die Instrumentierung erfolgt über `ProfilerHub`. Bei deaktiviertem Developer
Mode werden keine Messdatensätze angelegt. Dadurch bleibt der zusätzliche
Overhead gering und es entstehen keine neuen DI-Zyklen.

Aktuell instrumentiert sind insbesondere Filesystem-I/O, Cache-Store-Auflösung,
Modul-Discovery/Load, Queue-Worker, Scheduler sowie ausgewählte Enterprise-
Bootstrap-Operationen. Weitere Fachkomponenten können über
`ProfilerHub::start()/stop()` oder `ProfilerHub::record()` angeschlossen werden.

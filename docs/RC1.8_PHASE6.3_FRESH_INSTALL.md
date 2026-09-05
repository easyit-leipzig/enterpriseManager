# RC1.8 Phase 6.3 – Fresh-Install-Abnahme

`php tools/rc18-fresh-install-audit.php` prüft Installer, Schema-Reihenfolge, Schemaquellen, `.env.example`, First-Run-Brücken sowie DataForm- und Developer-Einstiege. Die tatsächliche MySQL/MariaDB-Ausführung bleibt bewusst eine separate Umgebungsabnahme; sie wird ohne Datenbank nicht vorgetäuscht.

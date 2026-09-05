# Validierung RC1.8-FC1-HF1

## Behobener Fehler

Installer `setup.php`: Ergebnisse von `SystemInspector` wurden mit falschen Array-Schlüsseln ausgewertet. Vor HF1 erschienen die Checks als `0` bis `7` und wurden fälschlich als `Fehler` dargestellt.

## Korrektur

- Checkname aus `name`
- Status aus `passed` (mit Legacy-Fallback `ok`)
- Pflichtstatus aus `required`
- Detailtext aus `message`
- fehlgeschlagene optionale Checks werden als `Optional` statt `Fehler` dargestellt
- Regressionstest `tests_rc18_phase3_installer2.php` schützt die Zuordnung der Inspector-Felder

## Automatisierte Abnahme

Durchgeführt in einer isolierten Linux/PHP-CLI-Prüfumgebung nach der Korrektur:

- Regressionstests: 49 PASS / 0 FAIL
- PHP-Syntaxprüfung: 719 PASS / 0 FAIL
- Release-Gates: 10 PASS / 0 FAIL

Release-Gates:

1. `tools/rc18-production-security-audit.php`
2. `tools/rc18-reproducibility-audit.php`
3. `tools/rc18-upgrade-migration-audit.php`
4. `tools/rc18-fresh-install-audit.php`
5. `tools/rc18-cross-component-audit.php`
6. `tools/rc18-integration-acceptance.php`
7. `tools/rc18-performance-audit.php`
8. `tools/architecture-audit.php`
9. `tools/rc18-final-release-gate.php`
10. `tools/rc18-final-candidate-audit.php`

## Reale Browser-Nachprüfung

Die konkrete Darstellung unter Windows/XAMPP muss nach Entpacken von HF1 erneut im Browser geprüft werden. Erwartet werden benannte Systemchecks (z. B. `php.version`, `extension.json`, `writable.storage`) statt numerischer Zeilen `0` bis `7`, dazu der tatsächliche Status und die Detailmeldung.

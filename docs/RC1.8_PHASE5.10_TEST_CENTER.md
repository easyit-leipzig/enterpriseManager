# RC1.8 Phase 5.10 – Developer Quality Center

Das Test Center gruppiert die vorhandenen Regressionstests nach technischen Bereichen:

- core
- modules
- installer
- cluster
- storage
- replication
- sdk
- developer
- master

CLI:

```bash
php easyit quality:center
php easyit quality:center --group=sdk
php easyit quality:center --json
```

Web: `app/developer/tests.php`

Statuswerte sind PASS, WARN und FAIL. FAIL liefert in der CLI Exit-Code 1 und kann damit als Release-Gate in CI/CD verwendet werden.

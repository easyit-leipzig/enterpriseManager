# HF16 – DataForm Runtime Execution Path Diagnosis & Repair

- Der tatsächlich ausgeführte DataForm-Frontcontroller `products/dataform/index.php` rendert die HTML-Shell nun selbst.
- Keine Abhängigkeit mehr vom separaten `WorkspaceLayout.php` im kritischen Hauptpfad.
- Sichtbarer Marker: `HF16 DATAFORM RUNTIME ACTIVE`.
- HTTP-Header: `X-EasyIT-DataForm-Runtime: HF16`.
- Neuer unabhängiger Diagnose-Endpunkt `products/dataform/runtime-proof.php` zeigt reale Pfade und SHA-256 des ausgelieferten Frontcontrollers.
- Workspace-CSS wird direkt und pfadrobust aus dem tatsächlichen `SCRIPT_NAME` referenziert.

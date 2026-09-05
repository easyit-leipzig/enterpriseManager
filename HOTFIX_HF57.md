# HOTFIX HF57 – Datensatzzeiger ohne CRUD-Löschsymbol

HF57 korrigiert die Wechselwirkung zwischen dem globalen 3D-CRUD-Styling und dem Datensatzzeiger aus HF56.

Ursache: Die globale CSS-Regel für `bulk_delete` formatierte bisher **jedes** `button` innerhalb des Sammellösch-Formulars als roten Löschbutton und fügte per `::before` ein `✖` ein. Da auch die Datensatzzeiger innerhalb dieses Formulars liegen, wurde der korrekte Zeiger überlagert.

Korrektur:
- Die `bulk_delete`-CRUD-Regeln gelten nur noch für den eigentlichen Button in `.bulk-toolbar`.
- Datensatzzeiger bleiben vollständig von CRUD-Icons und CRUD-Farben getrennt.
- Zustände bleiben exakt:
  - aktiv: `▶`
  - neu: `*`
  - unausgewählt: leer

Der Sammellösch-Button behält unverändert sein rotes 3D-Löschsymbol.

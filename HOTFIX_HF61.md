# HOTFIX HF61

## Inline-Bearbeitung bestehender Datensätze in der Tabellenansicht

HF61 erweitert die Datenblattansicht so, dass nicht nur die `*`-Neuzeile, sondern auch jeder bestehende Datensatz direkt in seiner Tabellenzeile bearbeitet werden kann.

- Bestehende Datensatzzeilen rendern die echten Eingabeelemente des jeweiligen Feldtyps.
- n:1-/1:n-Lookups bleiben Select-Felder mit ihren Anzeigewerten.
- Jede Zeile besitzt ein eigenes, vom Sammellösch-Formular getrenntes Save-Formular.
- `Speichern` aktualisiert exakt den Datensatz dieser Zeile und hält die Listenansicht offen.
- Der gespeicherte Datensatz bleibt über den Datensatzzeiger `▶` aktiv.
- Validierungsfehler verbleiben in derselben Tabellenzeile; die eingegebenen Werte gehen nicht verloren.
- Die `*`-Zeile bleibt ausschließlich für neue Datensätze zuständig.
- Fokus in einer Tabellenzeile markiert diese clientseitig als aktiv, ohne die laufende Eingabe durch einen Reload zu verlieren.

HF61 baut auf HF60 auf; alle physischen Tabellenfelder bleiben in der Datenblattansicht sichtbar.

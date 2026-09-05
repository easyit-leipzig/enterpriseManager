# HOTFIX HF55 – Feldtypgerechte Ausgabe in der Datensatz-Tabelle

HF55 stellt die ursprüngliche DataForm-Ausgabeform in der tabellarischen Datensatzansicht wieder her.

- Textfelder werden als schreibgeschützte `input type="text"` ausgegeben.
- Zahl, Datum, Datum/Uhrzeit, E-Mail und URL verwenden ihren passenden HTML-Input-Typ.
- Mehrzeilige Texte werden als schreibgeschützte `textarea` ausgegeben.
- Checkboxen werden als deaktivierte Checkbox mit Ja/Nein dargestellt.
- Select-Felder und relationale 1:n-/n:1-Lookups werden als deaktivierte Auswahlfelder mit dem aufgelösten Anzeigewert dargestellt.
- Abgeleitete Mehrfachauswahlen bleiben als mehrzeilige Ausgabe lesbar.
- Die Ausgabefelder besitzen bewusst kein `name` und sind read-only/disabled; Sammelaktionen können daher keine Felddaten versehentlich mitsenden.
- CSV-Export und Detail-/Bearbeitungsansicht bleiben unverändert.

Damit ist die Listenansicht nicht länger eine reine Texttabelle, sondern zeigt die Feldsemantik wieder unmittelbar an.

# easyIT Enterprise RC1.8-FC1-HF76 – PUBLISH16

## Kindformular: gekoppelte Eigenschaft vorausgewählt

PUBLISH16 korrigiert die Eltern-/Kind-Kopplung in der DataForm-Runtime und im exportierten HTML5-Anwenderpaket.

Bei einem über eine aktive 1:n-Beziehung geöffneten Kind-DataForm wird das konfigurierte FK-/Lookup-Feld jetzt sichtbar als vorausgewählte Eigenschaft dargestellt. Der aktuelle Eltern-Datensatz ist bereits gewählt. Das Auswahlfeld ist im Elternkontext gesperrt; der Wert wird zusätzlich über ein Hidden-Feld übertragen und serverseitig beim Speichern erzwungen.

Der Elternkontext gilt nun für die gesamte Kindansicht, nicht nur für die Neuanlage. Dadurch werden auch beim Bearbeiten vorhandener Kinddatensätze keine fremden Elternwerte angeboten und die Kindliste wird auf die Datensätze des aktuellen Eltern-Datensatzes eingeschränkt.

Im exportierten Anwenderpaket wird, sofern konfiguriert, das Anzeigefeld des Eltern-DataForms als Beschriftung der Vorauswahl verwendet; andernfalls wird die technische Datensatz-ID angezeigt.

## Prüfungen

- PUBLISH16 Spezialtest: 12/12 PASS
- gesamte `tests_*.php`-Suite: 151/151 PASS
- PHP-Syntaxprüfung: 860/860 PASS
- JavaScript-Syntaxprüfung: 5/5 PASS

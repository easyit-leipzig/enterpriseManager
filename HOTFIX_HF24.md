# RC1.8-FC1-HF24 – Automatic DataForm Secret Provisioning

HF23 verlangte für verschlüsselte MySQL-/Oracle-Datenquellen einen manuell
vorhandenen `APP_KEY` oder `DATAFORM_APP_KEY`.

HF24 macht die Schlüsselbereitstellung automatisch.

## Neuinstallation
Der EnterpriseInstaller erzeugt automatisch einen dedizierten
`DATAFORM_APP_KEY` aus 32 kryptografisch sicheren Zufallsbytes.

## Datenbank-Assistent
Ein vorhandener `DATAFORM_APP_KEY` wird beibehalten. Ist keiner vorhanden,
wird ein bestehender `APP_KEY` verwendet. Fehlen beide, wird automatisch ein
neuer `DATAFORM_APP_KEY` erzeugt.

## Bestehende Installation
Beim Öffnen des DataForm-Workspaces wird ein fehlender Schlüssel einmalig
nachprovisioniert, sofern `DataForm5-Core/.env` beschreibbar ist.

Der Schlüssel wird nicht im Browser ausgegeben, nicht ersetzt, wenn bereits
einer vorhanden ist, mit Dateisperre geschrieben und anschließend verifiziert.

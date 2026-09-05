# RC1.8-FC1-HF76 FIX1 – DataForm Include-Idempotenz

## Fehlerbild
Beim Aufruf von `products/dataform/runtime.php` konnte PHP mit folgendem Fatal Error abbrechen:

`Cannot declare class DataFormFieldTypeRegistry, because the name is already in use`

## Ursache
`DataFormManager.php` bindet `DataFormFieldTypes.php` bereits mit `require_once` ein. Mehrere HTTP-Einstiegspunkte, insbesondere `runtime.php`, `records.php` und `media.php`, banden dieselbe Datei danach erneut mit normalem `require` ein. Dadurch wurde `DataFormFieldTypeRegistry` im selben Request ein zweites Mal deklariert.

## Korrektur
Alle PHP-Abhängigkeiten der DataForm-HTTP-Einstiegspunkte werden nun idempotent mit `require_once` geladen. Damit bleiben auch indirekte Abhängigkeitsketten sicher, wenn eine Klasse bereits von einem Manager oder Transportmodul geladen wurde.

## Regressionstest
`tests_dataform_hf76_fix1_include_idempotency.php` reproduziert die problematische Include-Reihenfolge und prüft zusätzlich, dass alle DataForm-Einstiegspunkte keine einfachen `require`-Einbindungen für PHP-Abhängigkeiten mehr enthalten.

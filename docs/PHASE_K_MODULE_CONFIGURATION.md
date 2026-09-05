# RC1.7 Phase K – Modul-Konfiguration

Module können typisierte Konfigurationsschemata unter `config/schema.php` definieren. Laufzeitwerte werden außerhalb des Pakets in der Admin-Datenbank gespeichert.

Scopes: `enterprise`, `product`, `project`. Secret-Felder werden getrennt in `enterprise_module_secrets` gespeichert und mit AES-256-GCM verschlüsselt. Für das Speichern von Secrets muss `APP_KEY` oder `DATAFORM_APP_KEY` in `DataForm5-Core/.env` gesetzt sein.

Beispiel:
```php
return [
  'enabled' => ['type'=>'bool','default'=>true,'required'=>true],
  'endpoint' => ['type'=>'string','default'=>'https://example.test'],
  'api_key' => ['type'=>'string','default'=>'','secret'=>true],
];
```

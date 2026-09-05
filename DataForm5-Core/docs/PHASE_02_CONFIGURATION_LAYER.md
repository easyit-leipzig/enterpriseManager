# Phase 2 – Configuration Layer

## Ziel

Die Konfiguration des DataForm5-Core wird zentral, umgebungsabhängig und ohne Fremdbibliothek geladen. Produktcode liest keine `.env`-Dateien direkt und verwendet keine verstreuten `getenv()`-Aufrufe.

## Verbindliche Regeln

1. Geheimnisse und installationsabhängige Werte stehen in `.env`; die Datei wird nicht verteilt.
2. `.env.example` dokumentiert alle unterstützten Variablen.
3. Strukturierte Einstellungen liegen als PHP-Arrays in `config/`.
4. Anwendungscode greift ausschließlich über `DataForm5\Core\Config` zu.
5. Der Kernel lädt zuerst die Umgebung und danach die Konfiguration.
6. In Produktion darf ein generierter Konfigurationscache verwendet werden.

## Verwendung

```php
$kernel = require __DIR__ . '/../bootstrap/app.php';
$config = $kernel->container()->get(DataForm5\Core\Config::class);

$name = $config->get('app.name');
$timezone = $config->require('app.timezone');
```

## Umgebungswerte in Konfigurationsdateien

```php
use DataForm5\Core\Configuration\Env;

return [
    'debug' => Env::get('APP_DEBUG', false),
];
```

`Env::get()` wandelt `true`, `false`, `null`, Ganzzahlen und Fließkommazahlen automatisch in PHP-Typen um.

## Konfigurationscache

```php
$config->cache(__DIR__ . '/../storage/framework/cache/config.php');
```

Mit `CONFIG_CACHE=true` lädt der Kernel diese Datei, sofern sie existiert.

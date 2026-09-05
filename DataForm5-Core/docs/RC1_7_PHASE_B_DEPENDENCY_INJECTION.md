# RC1.7 Phase B – Dependency Injection & Service Container

## Ziel

Phase B stellt einen stabilen, modulübergreifenden Dependency-Injection-Vertrag bereit. Module sollen ihre Abhängigkeiten deklarieren, statt Infrastrukturklassen selbst mit `new` zu erzeugen.

## Öffentlicher Vertrag

Externe Module sollten `DataForm5\Core\Contracts\ContainerInterface` verwenden. Die konkrete Implementierung bleibt `ServiceContainer`.

Unterstützt werden:

- `bind()` für transiente Services,
- `singleton()` für gemeinsam genutzte Services,
- `instance()` für bereits erzeugte Instanzen,
- `alias()` für stabile Servicenamen,
- automatische Constructor Injection,
- Interface-Bindings,
- benannte Parameter-Overrides über `make()`,
- Methoden-/Callable-Injection über `call()`,
- Service-Tags über `tag()` und `tagged()`,
- Diagnose über `describe()` und `aliases()`,
- Erkennung zirkulärer Abhängigkeiten und Alias-Schleifen.

## Beispiel

```php
$container->singleton(LoggerInterface::class, FileLogger::class);
$service = $container->get(CustomerService::class);
```

`CustomerService` kann `LoggerInterface` im Konstruktor deklarieren. Die konkrete Implementierung muss ihm nicht bekannt sein.

## Modulregel

Neue easyIT-/DataForm-Module dürfen Infrastruktur-Services nicht global erzeugen, wenn diese bereits über den Container verfügbar sind. Service Provider bleiben der zentrale Ort für Bindings.

## Kompatibilität

Die bisherige API `bind`, `singleton`, `instance`, `has`, `get` und `build` bleibt erhalten. Bestehende Service Provider funktionieren ohne Migration weiter.

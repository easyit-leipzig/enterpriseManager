# Phase 8 – Event-System

Build: `build0008`

Die Event-Schicht entkoppelt Core-Dienste, Produkte und Module. Listener können nach Priorität ausgeführt werden. Stoppbare Events beenden die weitere Verarbeitung. Event-Abonnenten bündeln mehrere Listener in einer Klasse.

## Komponenten

- `EventDispatcherInterface`
- `EventSubscriberInterface`
- `StoppableEventInterface`
- `Event`
- `EventDispatcher`
- `EventServiceProvider`

## Beispiel

```php
$dispatcher->listen(UserSaved::class, function (UserSaved $event): void {
    // Reaktion auf das gespeicherte Benutzerobjekt
}, priority: 100);

$dispatcher->dispatch(new UserSaved($user));
```

Neben dem Klassennamen kann ein zusätzlicher Ereignisname dispatcht werden. Danach werden Klassen-, Elternklassen- und Interface-Listener berücksichtigt.

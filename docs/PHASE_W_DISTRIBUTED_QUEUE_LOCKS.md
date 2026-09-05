# Phase W – verteilte Queue und clusterweite Locks

Phase W erweitert die Cluster-Grundlage um zwei Bausteine, die für mehrere
gleichzeitig arbeitende Nodes notwendig sind:

1. `DatabaseQueue` für gemeinsam verarbeitete Jobs.
2. `DatabaseMutex` für clusterweite Scheduler- und Anwendungslocks.

Die bisherigen lokalen Provider bleiben vollständig erhalten.

## Queue

Lokaler Standard:

```text
QUEUE_CONNECTION=file
```

Verteilte Queue:

```text
QUEUE_CONNECTION=database
QUEUE_DB_HOST=127.0.0.1
QUEUE_DB_PORT=3306
QUEUE_DB_DATABASE=easyit_admin
QUEUE_DB_USERNAME=root
QUEUE_DB_PASSWORD=
QUEUE_DB_TABLE=enterprise_queue_jobs
```

Wenn `QUEUE_DB_*` nicht gesetzt sind, werden vorhandene `ADMIN_DB_*`-Werte als
Fallback verwendet.

`DatabaseQueue` reserviert Jobs transaktional. Ein Job wechselt dabei von
`pending` nach `processing`, bevor der Worker ihn erhält. Dadurch können
mehrere Worker dieselbe Queue verwenden, ohne denselben Datensatz gleichzeitig
zu beanspruchen.

## Clusterweite Locks

Lokaler Standard:

```text
SCHEDULER_MUTEX_DRIVER=file
```

Clusterbetrieb:

```text
SCHEDULER_MUTEX_DRIVER=database
SCHEDULER_DB_HOST=127.0.0.1
SCHEDULER_DB_PORT=3306
SCHEDULER_DB_DATABASE=easyit_admin
SCHEDULER_DB_USERNAME=root
SCHEDULER_DB_PASSWORD=
SCHEDULER_MUTEX_TABLE=enterprise_cluster_locks
CLUSTER_NODE_ID=node-a
```

Auch hier dienen `ADMIN_DB_*` als Fallback.

Der Scheduler verwendet jetzt `MutexInterface`. Daher kann dieselbe Scheduler-
Logik mit `FileMutex` oder `DatabaseMutex` laufen.

## Programmatische Cluster-Locks

```php
$lock = enterprise_cluster_lock();

$lock->synchronized('daily-import', function (): void {
    // Dieser Abschnitt darf clusterweit nur einmal gleichzeitig laufen.
}, 300);
```

Diagnose:

```bash
php tools/cluster-lock-test.php diagnostic 30
```

## Kompatibilität

- FileQueue bleibt verfügbar.
- SyncQueue bleibt verfügbar.
- FileMutex bleibt der Standard.
- Einzelserverinstallationen benötigen keine zusätzliche Konfiguration.
- Es wird kein Redis-Server vorausgesetzt.

## Grenzen dieser Phase

Die DatabaseQueue setzt für produktiven Clusterbetrieb eine gemeinsame
MySQL-/MariaDB-Datenbank voraus. Redis, externe Message Broker und echte
Node-to-node-Kommunikation werden bewusst noch nicht vorausgesetzt.

# Phase 13 – ORM- und Model-Layer

Build: `DataForm5-Core-build0013`

Diese Phase ergänzt den produktneutralen Core um eine schlanke ORM-Schicht über dem bestehenden `DatabaseInterface`.

## Bestandteile

- `Model` als Basisklasse für Domänenmodelle
- Mass-Assignment-Schutz über `fillable` und `guarded`
- Attribut-Casts für Boolean, Integer, Float, String, Array/JSON und DateTime
- Hydrierung gespeicherter Datensätze
- `ModelRepository` für CRUD, `find`, `where`, `findOrFail`, `hasMany` und `belongsTo`
- `OrmManager` für verbindungsbezogene Repositories
- `BelongsToMany` für n:m-Zuordnungen über den vorhandenen `RelationManager`
- Registrierung im zentralen Service-Container

## Beispiel

```php
final class Project extends Model
{
    protected static string $table = 'projects';
    protected array $fillable = ['name', 'active'];
    protected array $casts = ['active' => 'boolean'];
}

$orm = $kernel->container()->get(OrmManager::class);
$projects = $orm->repository(Project::class);
$project = $projects->create(['name' => 'Demo', 'active' => true]);
```

Die ORM-Schicht bleibt datenbankunabhängig und funktioniert mit CSV, MySQL/MariaDB, SQLite und Oracle, sofern der jeweilige Adapter das gemeinsame Datenbankinterface erfüllt.

# Phase X – Shared Storage und Storage-Provider

Phase X führt `StorageManager` als gemeinsame Dateispeicher-Abstraktion ein.

## Disks

### local

Lokaler Standard:

```text
STORAGE_DISK=local
STORAGE_LOCAL_ROOT=storage/app
```

### shared

Für NFS, SMB, SAN oder ein anderweitig gemeinsam gemountetes Dateisystem:

```text
STORAGE_DISK=shared
STORAGE_SHARED_ROOT=/mnt/easyit-shared
```

Unter Windows kann `STORAGE_SHARED_ROOT` auch auf ein gemountetes Laufwerk oder
einen geeigneten UNC-/Netzpfad zeigen, sofern PHP darauf zugreifen darf.

### s3

Eine S3-kompatible Konfiguration ist bereits vorgesehen:

```text
STORAGE_S3_ENDPOINT=
STORAGE_S3_BUCKET=
STORAGE_S3_REGION=
STORAGE_S3_ACCESS_KEY=
STORAGE_S3_SECRET_KEY=
```

Phase X implementiert absichtlich noch keinen eigenen S3-HTTP-Client. Der Driver
meldet deshalb `transport_provider_missing`, statt Netzwerkzugriffe nur teilweise
oder unsicher zu implementieren.

## Verwendung in Enterprise/Modulen

```php
$storage = enterprise_storage()->disk();
$storage->write('reports/result.json', $json);
$content = $storage->read('reports/result.json');
```

Bestimmte Disk:

```php
$shared = enterprise_storage()->disk('shared');
```

## Ziel

Module sollen keine fest verdrahteten Pfade wie `C:\xampp\...` oder
`/var/www/...` voraussetzen. Dadurch kann dieselbe Erweiterung auf Einzelservern
und Cluster-Nodes eingesetzt werden.

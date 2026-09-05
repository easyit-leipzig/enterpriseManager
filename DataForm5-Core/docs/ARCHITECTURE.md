# Verbindliche DataForm-5-Datenbankarchitektur

## Grundsatz

DataForm 5 arbeitet ausschließlich gegen Interfaces und Core-Dienste. Konkrete Treiber dürfen nur in der Factory und innerhalb des Adapterbereichs bekannt sein.

## Abhängigkeitsrichtung

`Admin/App/Projects -> 02_core -> 01_interfaces <- 03_adapters`

`04_extensions` ist optional und darf die Basisschnittstellen nicht brechen.

## Projekttrennung

Administrationsdaten und Projektdaten werden als getrennte Verbindungen konfiguriert. Beispielnamen sind `admin` und `project`. Ein Projekt darf seine Datenquelle wechseln, ohne dass Formulare oder Adminmodule angepasst werden müssen.

## CSV

Der CSV-Adapter verwaltet Tabellen als Dateien mit Kopfzeile, Pflichtfeld `id` und Trennzeichen `|`. 1:n- und n:m-Beziehungen werden über Metadaten und Pivot-Tabellen emuliert.

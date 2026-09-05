# Enterprise-Module

Dieser Ordner ist für produktübergreifende, installierbare Enterprise-Module vorgesehen.

## Regel

- Gemeinsame technische Infrastruktur gehört in `DataForm5-Core/system/`.
- Produktlogik gehört in `products/<produkt>/`.
- Installierbare, produktübergreifende Erweiterungen gehören hierher.
- Ein Modul muss ein Manifest, definierte Abhängigkeiten und eine reproduzierbare Installation besitzen.

Der aktuelle Master enthält noch keine ausgelagerten Enterprise-Module. Die Lizenzierungslogik liegt bewusst im Core; ihre Verwaltungsoberfläche liegt unter `app/licensing/`.

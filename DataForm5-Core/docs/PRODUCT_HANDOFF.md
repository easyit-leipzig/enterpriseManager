# Übergabe an Produkte und Module

Der finale Core liegt unter:

```text
easyIT-Enterprise/
└── DataForm5-Core/
```

Neue Produkte werden parallel angelegt:

```text
easyIT-Enterprise/
├── DataForm5-Core/
├── products/
│   ├── DataForm/
│   └── Dialog/
└── modules/
```

Produkte verwenden ausschließlich die öffentlichen Interfaces und Service-Provider des Core. Sie dürfen Dateien im Core nicht direkt überschreiben. Gemeinsame, optionale Funktionen gehören in Module; fachliche Funktionen gehören in das jeweilige Produkt.

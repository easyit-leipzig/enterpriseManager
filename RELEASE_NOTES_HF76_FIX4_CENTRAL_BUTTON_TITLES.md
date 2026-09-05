# HF76-FIX4 – zentrale Button-Titel

- Alle 52 Button-Bildtypen besitzen einen zentralen, aussagekräftigen Titel in `system/ui/ButtonRegistry.php`.
- `title` und `aria-label` werden aus dieser Registry erzeugt; lokale Titel werden nicht mehr als Quelle verwendet.
- Die Browser-Registry enthält keine zweite Kopie der Metadaten mehr, sondern liest die serverseitig erzeugten Registry-Daten.
- Dynamisch/legacy erzeugte Buttons werden ebenfalls auf den zentralen Titel normalisiert.
- Bildbuttons bleiben ohne farbigen CSS-Hintergrund.

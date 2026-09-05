# HF76 PUBLISH3 – Projektbezogener Anwenderpaket-Export

In der Enterprise-Projektliste steht pro Projekt die Aktion **Anwenderpaket exportieren** zur Verfügung. Sie erzeugt ein lauffähiges ZIP mit produktiver easyIT-/DataForm-Laufzeit, SQL-Snapshot der gewählten Projektdatenbank und ausschließlich den Filesystem-Medien dieses Projekts.

Das erzeugte ZIP enthält keine reale `.env` und keine DB-Kennwörter. Auf dem Zielsystem wird `setup.php` ausgeführt. Der Installer übernimmt die dort eingegebenen DB-Zugangsdaten auch für die Projektdatenbank, importiert den Bundle-Snapshot und registriert das enthaltene Projekt automatisch. Die ursprüngliche Projekt-ID wird bei einer frischen Installation erhalten, damit bestehende Media-Pfade stabil bleiben.

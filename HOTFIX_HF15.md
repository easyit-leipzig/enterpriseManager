# HF15 – Installer Form Persistence & Password Visibility
- Nach Validierungs-/Installationsfehlern bleiben die bereits eingegebenen Formularwerte erhalten.
- Das betrifft DB-Verbindung, Admin-Stammdaten, Produktauswahl, Umgebung und Zeitzone.
- Passwortwerte werden nur für die unmittelbar erneut dargestellte POST-Antwort übernommen und nicht in Session oder Datei persistiert.
- Alle Passwortfelder in Setup und Erstadministrator besitzen eine Augenfunktion.

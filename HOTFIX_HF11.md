# RC1.8-FC1-HF11 – Project Provisioning

- Projektbereich trennt jetzt **Neues Projekt anlegen** und **Vorhandenes Projekt registrieren**.
- Neuanlage erzeugt eine neue MariaDB/MySQL-Projektdatenbank.
- `installer/schema/project/*.php` wird in natürlicher Reihenfolge angewendet und in `migrations` registriert.
- Das Enterprise-Projekt wird erst nach erfolgreicher Datenbank- und Schema-Provisionierung registriert.
- Bei Fehlern wird eine neu erzeugte Datenbank wieder entfernt.
- Existierende Datenbanken werden bewusst nicht überschrieben.
- Audit `project.provision` und Event `project.provisioned` ergänzt.

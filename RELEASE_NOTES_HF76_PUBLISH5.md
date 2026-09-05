# HF76-PUBLISH5 – Eltern-/Kindbeziehungen im Anwenderpaket

Die minimale HTML5-Projektruntime bildet 1:n-Beziehungen jetzt vollständig ab.

- Auswahl eines Eltern-Datensatzes lädt ohne Seitenreload alle zugehörigen Kinddatensätze.
- Mehrere 1:n-Beziehungen eines Eltern-DataForms werden als getrennte Kindbereiche angezeigt.
- Zuordnung erfolgt über `dataform_relations.lookup_field_id` und die Eltern-ID.
- Kindbereiche enthalten alle passenden Kinddatensätze sowie Anzeigen-/Bearbeiten-Aktionen.
- Ein neuer Kinddatensatz kann direkt aus dem Elternkontext angelegt werden.
- Die Kindseite wird im Elternkontext auf die zugehörigen Datensätze gefiltert.
- Das Fremdschlüsselfeld ist im Elternkontext gesperrt und wird serverseitig erzwungen.

# RC1.8-FC1-HF76 PUBLISH18 – gebundene Inline-Neuzeile und Pagination

- Die Tabellen-*‑Neuzeile eines 1:n-Kindformulars übernimmt im realen Parent-Kontext unmittelbar die technische Eltern-ID. Bei Eltern-ID 9201 wird sichtbar `#9201` vorausgewählt; der generische Platzhalter „Eltern-Datensatz wählen“ ist in diesem Zustand nicht mehr die sichtbare Auswahl.
- Die Einstellung `Gebundenes Fremdschlüsselfeld schreibgeschützt` wurde direkt in den 1:n-Beziehungsdesigner integriert. Voreinstellung ist `Ja`. Bestehende Beziehungen ohne explizite Einstellung werden ebenfalls als read-only behandelt.
- Bei read-only wird die Parent-ID clientseitig sichtbar vorgegeben und serverseitig erzwungen. Bei deaktiviertem read-only ist die Parent-ID die Anfangsbelegung, kann aber bewusst geändert werden.
- Die Paginierung der Tabellenansicht wird als eigene Tabellenzeile direkt nach den gespeicherten Datensätzen und vor der festen `*`-Neuzeile gerendert.
- Enterprise-Runtime, Realvorschau und exportierte HTML5-Runtime verwenden dieselbe Parent-Bindungs- und Pagination-Semantik.

# RC1.8-FC1-HF18 – DataForm Render Boundary Repair

Der Browserbefund ließ sich als Abbruch innerhalb des gepufferten
Designer-Templates einordnen: Der bis dahin erzeugte Puffer wurde sichtbar,
während die nachgelagerte HTML-Shell mit CSS nie erreicht wurde.

HF18:
- fängt `Throwable` während des kompletten Workspace-Renderings ab,
- verwirft in diesem Fall den unvollständigen Teilpuffer,
- rendert die Enterprise-Shell trotzdem,
- protokolliert den konkreten Renderfehler,
- normalisiert historische und fehlerhafte Feld-Konfigurationen,
- normalisiert insbesondere String-/Array-Formen von `options`,
- schützt `width` und `placeholder`,
- trägt den sichtbaren Marker `HF18 DATAFORM RUNTIME ACTIVE`.

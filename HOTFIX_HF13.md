# RC1.8-FC1-HF13 – DataForm UI/Layout Integration

- Enterprise-CSS wird weiterhin extern geladen und zusätzlich als serverseitiger Critical-CSS-Fallback eingebettet.
- DataForm Workspace und Formular-Designer bleiben dadurch auch bei fehlerhafter relativer Asset-Auflösung vollständig gestaltet.
- Veraltete `render_page()`-Aufrufe in Workflow und grafischem Workflow-Designer auf die aktuelle Layout-API umgestellt.
- Regressionstest für DataForm Workspace/Designer ergänzt.

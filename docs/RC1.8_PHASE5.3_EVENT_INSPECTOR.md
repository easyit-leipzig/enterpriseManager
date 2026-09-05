# RC1.8 Phase 5.3 – Event Inspector

Der Event Inspector zeigt Eventkatalog, Listenerzahlen, Listener-Prioritäten und
den Dispatch-Trace des aktuellen Requests.

Der Inspector verwendet dieselbe Enterprise-EventDispatcher-Instanz wie
`enterprise_event_listen()` und `enterprise_event_dispatch()`.

Web: `app/developer/events.php`

CLI: `php tools/developer-events.php [Suchbegriff]`

Der Dispatch-Trace wird nur bei aktivem Developer Mode aufgezeichnet.
Der Hook Inspector folgt in Phase 5.4.

<?php
declare(strict_types=1);

/**
 * PUBLISH19: DataForm-weite Schalter fuer Volltextsuche und Filter.
 * show_search ist der Volltextsuche-Schalter; show_filter steuert Feld-
 * und gespeicherte Filter unabhaengig davon.
 */
return static function (PDO $pdo): void {
    $exists=$pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dataforms' AND COLUMN_NAME=?"
    );

    $exists->execute(['show_search']);
    if ((int)$exists->fetchColumn()===0) {
        $pdo->exec(
            "ALTER TABLE dataforms ADD COLUMN show_search "
            ."TINYINT(1) NOT NULL DEFAULT 1 AFTER default_per_page"
        );
    }

    $exists->execute(['show_filter']);
    if ((int)$exists->fetchColumn()===0) {
        $pdo->exec(
            "ALTER TABLE dataforms ADD COLUMN show_filter "
            ."TINYINT(1) NOT NULL DEFAULT 1 AFTER show_search"
        );
    }
};

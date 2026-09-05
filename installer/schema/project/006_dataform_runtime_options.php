<?php
declare(strict_types=1);
return static function (PDO $pdo): void {
    $columns = [
        'view_mode' => "VARCHAR(20) NOT NULL DEFAULT 'table'",
        'default_per_page' => "INT UNSIGNED NOT NULL DEFAULT 20",
        'show_search' => "TINYINT(1) NOT NULL DEFAULT 1",
        'show_pagination' => "TINYINT(1) NOT NULL DEFAULT 1",
        'allow_create' => "TINYINT(1) NOT NULL DEFAULT 1",
        'allow_edit' => "TINYINT(1) NOT NULL DEFAULT 1",
        'allow_delete' => "TINYINT(1) NOT NULL DEFAULT 1",
        'dialog_size' => "VARCHAR(20) NOT NULL DEFAULT 'large'",
        'css_class' => "VARCHAR(160) NULL",
        'additional_css' => "LONGTEXT NULL",
        'event_handlers_json' => "LONGTEXT NULL",
    ];
    $exists=$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dataforms' AND COLUMN_NAME=?");
    foreach($columns as $name=>$definition){
        $exists->execute([$name]);
        if((int)$exists->fetchColumn()===0){
            $pdo->exec('ALTER TABLE dataforms ADD COLUMN `'.$name.'` '.$definition);
        }
    }
};

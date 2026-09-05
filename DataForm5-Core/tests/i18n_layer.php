<?php
declare(strict_types=1);
use DataForm5\I18n\Contracts\TranslatorInterface;use DataForm5\I18n\Core\LocaleFormatter;
$kernel=require __DIR__.'/../bootstrap/app.php';$c=$kernel->container();$t=$c->get(TranslatorInterface::class);
assert($t->get('messages.welcome',['name'=>'Olaf'])==='Willkommen, Olaf!');
assert($t->get('messages.only_english')==='Fallback works');
$t->setLocale('en_US');assert($t->get('messages.project.created',['name'=>'Demo'])==='Project Demo was created.');
$f=$c->get(LocaleFormatter::class);assert($f->number(1234.5,1)!=='');assert($f->currency(12.5,'EUR')!=='');assert($f->date('2026-08-05')!=='');
echo "PASS: Internationalization and Localization Layer\n";

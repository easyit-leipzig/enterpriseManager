<?php
declare(strict_types=1);
use DataForm5\Mail\Contracts\NotificationInterface;use DataForm5\Mail\Core\{MailManager,Message,NotificationManager,TemplateRenderer};
require_once __DIR__.'/../bootstrap/autoload.php';
$kernel=require __DIR__.'/../bootstrap/app.php';$c=$kernel->container();$mail=$c->get(MailManager::class);$mail->send((new Message())->to('test@example.com','Tester')->subject('Phase 17')->text('OK'),'null');
$tmp=__DIR__.'/../storage/test-runtime/mail-template.php';@mkdir(dirname($tmp),0775,true);file_put_contents($tmp,'Hallo <?= htmlspecialchars($name, ENT_QUOTES) ?>');assert($c->get(TemplateRenderer::class)->render($tmp,['name'=>'Olaf'])==='Hallo Olaf');
$n=new class implements NotificationInterface{public function toMail(array $u):Message{return (new Message())->to($u['email'])->subject('Hinweis')->text('Hallo '.$u['name']);}};$c->get(NotificationManager::class)->send(['email'=>'test@example.com','name'=>'Olaf'],$n,'null');
assert($c->get(DataForm5\Mail\Contracts\MailerInterface::class) instanceof DataForm5\Mail\Contracts\MailerInterface);
echo "PASS: Mail and Notification Layer\n";

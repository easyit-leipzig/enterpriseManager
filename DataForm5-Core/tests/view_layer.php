<?php
declare(strict_types=1);
use DataForm5\View\Contracts\ViewFactoryInterface;use DataForm5\View\Core\ThemeManager;use DataForm5\View\Exceptions\ViewException;
$kernel=require dirname(__DIR__).'/bootstrap/app.php';$views=$kernel->container()->get(ViewFactoryInterface::class);
assert($views->exists('pages.demo'));$html=$views->make('pages.demo',['title'=>'Test','heading'=>'Hallo <Core>','message'=>'<script>alert(1)</script>']);
assert(str_contains($html,'Hallo &lt;Core&gt;'));assert(str_contains($html,'&lt;script&gt;alert(1)&lt;/script&gt;'));assert(!str_contains($html,'<script>alert(1)</script>'));assert(str_contains($html,'themes/default/assets/app.css'));
$themes=$kernel->container()->get(ThemeManager::class);$themes->use('dark');$html=$views->make('pages.demo',['heading'=>'Dark','message'=>'ok']);assert(str_contains($html,'themes/dark/assets/app.css'));
$failed=false;try{$views->make('../secret');}catch(ViewException){$failed=true;}assert($failed);echo "PASS: View, Template and Rendering Layer\n";

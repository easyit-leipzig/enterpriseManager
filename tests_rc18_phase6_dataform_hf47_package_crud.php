<?php
declare(strict_types=1);
$root=__DIR__;
$page=(string)file_get_contents($root.'/products/dataform/packages.php');
$manager=(string)file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$checks=[];
function hf47_check(string $name,bool $ok): void {global $checks;$checks[]=[$name,$ok];echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;}
hf47_check('HF47 marker',str_contains($page,'DataForm Workspace · HF47'));
hf47_check('stored package repository is visible',str_contains($page,'Gespeicherte Pakete'));
hf47_check('CRUD legend is visible',str_contains($page,'<strong>C</strong> Export oben')&&str_contains($page,'<strong>R</strong> Herunterladen')&&str_contains($page,'<strong>U</strong> Bearbeiten')&&str_contains($page,'<strong>D</strong> Löschen'));
hf47_check('CREATE uses graphical CRUD marker',str_contains($page,'data-crud="create"'));
hf47_check('export refreshes package repository after download',str_contains($page,'window.location.reload()')&&str_contains($page,'URL.createObjectURL(blob)'));
hf47_check('READ download action exists',str_contains($page,'data-crud="read"')&&str_contains($page,'download_package='));
hf47_check('UPDATE edit and save actions exist',str_contains($page,'data-crud="edit"')&&str_contains($page,'action" value="rename_package"')&&str_contains($page,'data-crud="save"'));
hf47_check('DELETE action exists',str_contains($page,'data-crud="delete"')&&str_contains($page,'action" value="delete_package"'));
hf47_check('audit history is explicitly immutable',str_contains($page,'Unveränderliches Auditprotokoll'));
hf47_check('manager lists physical saved packages',str_contains($manager,'function storedPackages(')&&str_contains($manager,"glob(\$dir.'/*.dfpkg')"));
hf47_check('download validates stored package',str_contains($manager,'function storedPackageForDownload(')&&str_contains($manager,'resolveStoredPackagePath('));
hf47_check('rename updates manifest metadata',str_contains($manager,'function renameStoredPackage(')&&str_contains($manager,"\$manifest['packageName']=\$newPackageName")&&str_contains($manager,"replaceZipString(\$zip,'manifest.json'"));
hf47_check('rename updates project metadata and README',str_contains($manager,"replaceZipString(\$zip,'project.json'")&&str_contains($manager,"replaceZipString(\$zip,'README.txt'"));
hf47_check('rename preserves package id implicitly',!str_contains($manager,"\$manifest['packageId']=bin2hex"));
hf47_check('delete removes physical package and logs audit',str_contains($manager,'function deleteStoredPackage(')&&str_contains($manager,"self::log(\n            \$pdo,\$manifest,'delete'"));
hf47_check('stored operations enforce project ownership',substr_count($manager,'manifestBelongsToProject(')>=4);
hf47_check('stored filename is traversal-safe',str_contains($manager,'basename($file)!==$file')&&str_contains($manager,"preg_match('/^[A-Za-z0-9._-]+\\.dfpkg$/i'"));
hf47_check('Windows-safe replacement path exists',str_contains($manager,'Windows cannot reliably rename a file over an existing target'));
$failed=array_filter($checks,static fn(array $row):bool=>!$row[1]);
echo PHP_EOL.count($checks).' Tests, '.count($failed).' Fehler'.PHP_EOL;
exit($failed?1:0);

<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\Service;

use EasyIT\LicenseServer\ProtectedCore\DefinitionValidator;
use EasyIT\LicenseServer\Support\Id;
use PDO;

final class DesignerService
{
    public function __construct(private PDO $pdo,private LicenseService $licenses,private AuditService $audit,private DefinitionValidator $validator){}

    private function authorize(array $installation):array
    {
        $license=$this->licenses->byId((string)$installation['license_id']);
        $this->licenses->requireModule((string)$license['license_id'],'dataform');
        $this->licenses->requireModule((string)$license['license_id'],'designer');
        return $license;
    }

    public function validate(array $installation,array $payload):array
    {
        $this->authorize($installation);
        $definition=$payload['definition']??null;
        if(!is_array($definition))throw new \RuntimeException('REQUEST_INVALID');
        return $this->validator->validate($definition);
    }

    public function compile(array $installation,array $payload,string $requestId):array
    {
        $this->authorize($installation);
        $project=(string)($payload['project_id']??'');$dataform=(string)($payload['dataform_id']??'');$definition=$payload['definition']??null;
        if($project===''||$dataform===''||!is_array($definition))throw new \RuntimeException('REQUEST_INVALID');
        $validation=$this->validator->validate($definition);if(!$validation['valid'])return ['compiled'=>false,'validation'=>$validation];
        $json=json_encode($definition,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);$hash=hash('sha256',$json);$id=Id::make('DEF');$now=time();
        $this->pdo->beginTransaction();
        try{
            $st=$this->pdo->prepare('SELECT COALESCE(MAX(revision_number),0) n FROM dataform_definitions WHERE project_id=? AND dataform_id=?');$st->execute([$project,$dataform]);$rev=(int)($st->fetch()['n']??0)+1;
            $ins=$this->pdo->prepare('INSERT INTO dataform_definitions (definition_id,project_id,dataform_id,revision_number,status,definition_json,definition_hash,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?)');$ins->execute([$id,$project,$dataform,$rev,'compiled',$json,$hash,$now,$now]);
            $this->audit->write('DATAFORM_COMPILED','dataform_definition',$id,'installation',(string)$installation['installation_id'],$requestId,null,json_encode(['project_id'=>$project,'dataform_id'=>$dataform,'revision_number'=>$rev]));
            $this->pdo->commit();
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
        return ['compiled'=>true,'definition_id'=>$id,'revision_number'=>$rev,'definition_hash'=>'sha256:'.$hash,'validation'=>$validation];
    }

    public function publish(array $installation,array $payload,string $requestId):array
    {
        $this->authorize($installation);$id=(string)($payload['definition_id']??'');if($id==='')throw new \RuntimeException('REQUEST_INVALID');
        $st=$this->pdo->prepare('SELECT * FROM dataform_definitions WHERE definition_id=? LIMIT 1');$st->execute([$id]);$row=$st->fetch();if(!$row)throw new \RuntimeException('DATAFORM_DEFINITION_NOT_FOUND');if(!in_array((string)$row['status'],['compiled','published'],true))throw new \RuntimeException('DATAFORM_REVISION_NOT_PUBLISHABLE');
        $this->pdo->beginTransaction();
        try{
            $ret=$this->pdo->prepare("UPDATE dataform_definitions SET status='retired',updated_at=? WHERE project_id=? AND dataform_id=? AND status='published' AND definition_id<>?");$ret->execute([time(),$row['project_id'],$row['dataform_id'],$id]);
            $up=$this->pdo->prepare("UPDATE dataform_definitions SET status='published',updated_at=? WHERE definition_id=?");$up->execute([time(),$id]);
            $this->audit->write('DATAFORM_PUBLISHED','dataform_definition',$id,'installation',(string)$installation['installation_id'],$requestId);
            $this->pdo->commit();
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
        return ['published'=>true,'definition_id'=>$id,'project_id'=>$row['project_id'],'dataform_id'=>$row['dataform_id'],'revision_number'=>(int)$row['revision_number']];
    }

    public function revisions(array $installation,array $payload):array
    {
        $this->authorize($installation);$project=(string)($payload['project_id']??'');$dataform=(string)($payload['dataform_id']??'');if($project===''||$dataform==='')throw new \RuntimeException('REQUEST_INVALID');
        $st=$this->pdo->prepare('SELECT definition_id,revision_number,status,definition_hash,created_at,updated_at FROM dataform_definitions WHERE project_id=? AND dataform_id=? ORDER BY revision_number DESC');$st->execute([$project,$dataform]);return ['revisions'=>$st->fetchAll()];
    }
}

<?php
declare(strict_types=1);
namespace EasyIT\Enterprise\Licensing;

final class DataFormDesignerClient
{
    public function __construct(private LicenseApiClient $api) {}
    public function validate(array $definition):array{return $this->api->post('/api/v1/designer/validate',['definition'=>$definition]);}
    public function compile(string|int $projectId,string|int $dataformId,array $definition):array{return $this->api->post('/api/v1/designer/compile',['project_id'=>(string)$projectId,'dataform_id'=>(string)$dataformId,'definition'=>$definition]);}
    public function publish(string $definitionId):array{return $this->api->post('/api/v1/designer/publish',['definition_id'=>$definitionId]);}
    public function revisions(string|int $projectId,string|int $dataformId):array{return $this->api->post('/api/v1/designer/revisions',['project_id'=>(string)$projectId,'dataform_id'=>(string)$dataformId]);}
}

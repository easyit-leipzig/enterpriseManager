<?php
declare(strict_types=1);
use DataForm5\OpenApi\Contracts\OpenApiGeneratorInterface;use DataForm5\OpenApi\Core\Operation;use DataForm5\OpenApi\Core\Schema;
$kernel=require dirname(__DIR__).'/bootstrap/app.php';$api=$kernel->container()->get(OpenApiGeneratorInterface::class);
$api->info('Project API','1.0.0','Test API')->server('https://example.test/api')->schema('Project',Schema::object(['id'=>Schema::integer(),'name'=>Schema::string()],['id','name']))->operation('GET','/projects/{id}',Operation::make('Projekt lesen',['200'=>Operation::jsonResponse('Projekt',Schema::ref('Project'))],['parameters'=>[Operation::pathParameter('id',Schema::integer())]]));
$doc=$api->generate();assert($doc['openapi']==='3.1.0');assert($doc['info']['title']==='Project API');assert(isset($doc['paths']['/projects/{id}']['get']));assert($doc['components']['schemas']['Project']['required']===['id','name']);$json=$api->toJson();assert(str_contains($json,'"Project API"'));echo "PASS: OpenAPI and API Documentation Layer\n";

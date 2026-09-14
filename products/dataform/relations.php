<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/system/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/system/ui/layout.php';
require_once __DIR__ . '/system/RelationManager.php';
/* HF38 compatibility contract: require __DIR__ . '/system/DataFormManager.php'; */
require_once __DIR__ . '/system/DataFormManager.php';
require_once __DIR__ . '/system/DataFormConfigNavigation.php';

$user=enterprise_require_auth('../../');
$projectId=(int)(
    $_GET['project']
    ?? $_POST['project']
    ?? ($_SESSION['active_project_id']??0)
);
$dataformId=(int)($_GET['dataform']??$_POST['dataform']??0);

$error='';
$success='';
$info='';
$project=null;
$dataforms=[];
$relations=[];
$fieldsByForm=[];
$baseTables=[];
$baseTableColumns=[];
$editRelation=null;
$editId=(int)($_GET['edit']??0);

try {
    $adminPdo=enterprise_pdo();
    enterprise_upgrade($adminPdo);

    if ($projectId<1) {
        throw new RuntimeException('Kein Projekt ausgewählt.');
    }

    $stmt=$adminPdo->prepare(
        'SELECT * FROM projects
         WHERE id=? AND product_type=?
         LIMIT 1'
    );
    $stmt->execute([$projectId,'dataform']);
    $project=$stmt->fetch();

    if (!$project) {
        throw new RuntimeException(
            'Das DataForm-Projekt wurde nicht gefunden.'
        );
    }

    $env=enterprise_env(
        dirname(__DIR__,2).'/DataForm5-Core/.env'
    );
    $pdo = enterprise_project_store_for_project($env, $project);

    RelationManager::ensureSchema($pdo);

    // HF38: before relation validation, reconcile every persistent table
    // binding with the real table schema. This registers physical columns
    // that exist in MariaDB but are missing from dataform_fields.
    $fieldSync=DataFormManager::synchronizeAllBoundTableFields($pdo);
    $repair=RelationManager::repairLegacyOneToMany($pdo);

    if ((int)$fieldSync['created']>0 || (int)$fieldSync['linked']>0) {
        $info=(int)$fieldSync['created'].' fehlende physische Tabellenfeld(er) wurden als DataForm-Felder registriert';
        if ((int)$fieldSync['linked']>0) {
            $info.='; '.(int)$fieldSync['linked'].' vorhandene Feldbindung(en) wurden synchronisiert';
        }
        $info.='.';
    }
    if (!empty($fieldSync['warnings'])) {
        $info=trim($info.' '.implode(' ',(array)$fieldSync['warnings']));
    }

    if ((int)$repair['repaired']>0) {
        $info=trim($info.' '.(int)$repair['repaired']
            .' bestehende 1:n-Beziehung(en) wurden automatisch geprüft und repariert.');
    }
    if (!empty($repair['warnings'])) {
        $info=trim(
            $info.' '.implode(' ',(array)$repair['warnings'])
        );
    }

    $dataforms=$pdo->query(
        'SELECT id,name,slug FROM dataforms ORDER BY name'
    )->fetchAll();

    // HF43: n:1 lookups can use a physical project base table directly.
    $baseTables=RelationManager::selectableBaseTables($pdo);
    foreach ($baseTables as $baseTable) {
        $baseTableColumns[$baseTable]=RelationManager::selectableBaseTableColumns(
            $pdo,
            $baseTable
        );
    }

    // HF38: dropdowns contain synchronized, runtime-usable fields. For a
    // table-bound DataForm this means the physical column must really exist.
    foreach (RelationManager::selectableFields($pdo) as $field) {
        $fieldsByForm[(int)$field['dataform_id']][]=$field;
    }

    if ($_SERVER['REQUEST_METHOD']==='POST') {
        enterprise_check_csrf(
            (string)($_POST['csrf']??'')
        );
        $action=(string)($_POST['action']??'');

        if ($action==='create_relation') {
            $name=trim((string)($_POST['name']??''));
            $type=(string)($_POST['relation_type']??'1:n');
            $sourceId=(int)($_POST['source_dataform_id']??0);
            $targetId=(int)($_POST['target_dataform_id']??0);
            $displayFieldId=(int)(
                $_POST['target_display_field_id']??0
            );
            $childFkFieldId=(int)(
                $_POST['child_fk_field_id']??0
            );
            $lookupSourceFieldId=(int)(
                $_POST['lookup_source_field_id']??0
            );
            $lookupDisplayFieldId=(int)(
                $_POST['lookup_display_field_id']??0
            );
            $lookupSourceKind=(string)($_POST['lookup_source_kind']??'dataform');
            $lookupTable=trim((string)($_POST['lookup_table']??''));
            $lookupKeyColumn=trim((string)($_POST['lookup_key_column']??'id'));
            $lookupDisplayColumn=trim((string)($_POST['lookup_display_column']??''));
            $required=isset($_POST['is_required']);
            // PUBLISH18: bei 1:n standardmäßig gekoppelt/read-only.
            $boundFieldReadonly=isset($_POST['bound_field_readonly']);
            $createLookup=isset($_POST['create_lookup']);
            $newChildFkName=trim((string)($_POST['new_child_fk_name']??''));

            if (
                $name===''
                || !in_array($type,['1:n','n:1','n:m'],true)
            ) {
                throw new RuntimeException(
                    'Name oder Beziehungstyp ist ungültig.'
                );
            }

            if ($type==='1:n') {
                RelationManager::createOneToMany(
                    $pdo,
                    $name,
                    $sourceId,
                    $targetId,
                    $displayFieldId,
                    $childFkFieldId,
                    $createLookup,
                    $required,
                    $newChildFkName,
                    $boundFieldReadonly
                );
                $success='Die 1:n-Beziehung wurde angelegt: '
                    .'Elternschlüssel id → Kind-Fremdschlüsselfeld.';
            } elseif ($type==='n:1') {
                if ($lookupSourceKind==='base_table') {
                    RelationManager::createManyToOneBaseTable(
                        $pdo,
                        $name,
                        $sourceId,
                        $lookupSourceFieldId,
                        $lookupTable,
                        $lookupKeyColumn,
                        $lookupDisplayColumn,
                        $required
                    );
                    $success='Die n:1-/Lookup-Beziehung wurde angelegt: '
                        .'Ausgangsfeld → Basistabelle.'.$lookupKeyColumn.'.';
                } else {
                    RelationManager::createManyToOne(
                        $pdo,
                        $name,
                        $sourceId,
                        $targetId,
                        $lookupSourceFieldId,
                        $lookupDisplayFieldId,
                        $required
                    );
                    $success='Die n:1-/Lookup-Beziehung wurde angelegt: '
                        .'Ausgangsfeld → Lookup-Datensatz.id.';
                }
            } else {
                if (
                    $sourceId<1
                    || $targetId<1
                    || $sourceId===$targetId
                ) {
                    throw new RuntimeException(
                        'Wählen Sie zwei unterschiedliche DataForms.'
                    );
                }

                $validForms=array_map(
                    static fn(array $form): int => (int)$form['id'],
                    $dataforms
                );
                if (
                    !in_array($sourceId,$validForms,true)
                    || !in_array($targetId,$validForms,true)
                ) {
                    throw new RuntimeException(
                        'Ein ausgewähltes DataForm existiert nicht.'
                    );
                }

                $sourceSlug='';
                $targetSlug='';
                foreach ($dataforms as $form) {
                    if ((int)$form['id']===$sourceId) {
                        $sourceSlug=(string)$form['slug'];
                    }
                    if ((int)$form['id']===$targetId) {
                        $targetSlug=(string)$form['slug'];
                    }
                }

                $junction='rel_'.str_replace(
                    '-',
                    '_',
                    $sourceSlug.'_'.$targetSlug
                );

                $insert=$pdo->prepare(
                    'INSERT INTO dataform_relations
                     (name,relation_type,source_dataform_id,
                      target_dataform_id,junction_name,
                      is_required,configuration_json)
                     VALUES (?,?,?,?,?,?,?)'
                );
                $insert->execute([
                    $name,
                    'n:m',
                    $sourceId,
                    $targetId,
                    $junction,
                    $required?1:0,
                    json_encode(
                        ['semantics'=>'many-to-many-v1'],
                        JSON_UNESCAPED_UNICODE
                        |JSON_UNESCAPED_SLASHES
                    ),
                ]);

                $success='Die n:m-Beziehung wurde angelegt.';
            }
        } elseif ($action==='update_relation') {
            $id=(int)($_POST['id']??0);
            $name=trim((string)($_POST['name']??''));
            $type=(string)($_POST['relation_type']??'1:n');
            $sourceId=(int)($_POST['source_dataform_id']??0);
            $targetId=(int)($_POST['target_dataform_id']??0);
            $displayFieldId=(int)($_POST['target_display_field_id']??0);
            $childFkFieldId=(int)($_POST['child_fk_field_id']??0);
            $lookupSourceFieldId=(int)($_POST['lookup_source_field_id']??0);
            $lookupDisplayFieldId=(int)($_POST['lookup_display_field_id']??0);
            $lookupSourceKind=(string)($_POST['lookup_source_kind']??'dataform');
            $lookupTable=trim((string)($_POST['lookup_table']??''));
            $lookupKeyColumn=trim((string)($_POST['lookup_key_column']??'id'));
            $lookupDisplayColumn=trim((string)($_POST['lookup_display_column']??''));
            $required=isset($_POST['is_required']);
            // PUBLISH18: bei 1:n standardmäßig gekoppelt/read-only.
            $boundFieldReadonly=isset($_POST['bound_field_readonly']);
            $createLookup=isset($_POST['create_lookup']);
            $newChildFkName=trim((string)($_POST['new_child_fk_name']??''));

            if ($id<1 || $name==='' || !in_array($type,['1:n','n:1','n:m'],true)) {
                throw new RuntimeException('Die zu bearbeitende Beziehung ist ungültig.');
            }

            if ($type==='1:n') {
                RelationManager::updateOneToMany(
                    $pdo,
                    $id,
                    $name,
                    $sourceId,
                    $targetId,
                    $displayFieldId,
                    $childFkFieldId,
                    $createLookup,
                    $required,
                    $newChildFkName,
                    $boundFieldReadonly
                );
                $success='Die 1:n-Beziehung wurde aktualisiert.';
            } elseif ($type==='n:1') {
                if ($lookupSourceKind==='base_table') {
                    RelationManager::updateManyToOneBaseTable(
                        $pdo,
                        $id,
                        $name,
                        $sourceId,
                        $lookupSourceFieldId,
                        $lookupTable,
                        $lookupKeyColumn,
                        $lookupDisplayColumn,
                        $required
                    );
                } else {
                    RelationManager::updateManyToOne(
                        $pdo,
                        $id,
                        $name,
                        $sourceId,
                        $targetId,
                        $lookupSourceFieldId,
                        $lookupDisplayFieldId,
                        $required
                    );
                }
                $success='Die n:1-/Lookup-Beziehung wurde aktualisiert.';
            } else {
                if ($sourceId<1 || $targetId<1 || $sourceId===$targetId) {
                    throw new RuntimeException('Wählen Sie zwei unterschiedliche DataForms.');
                }
                $sourceSlug='';
                $targetSlug='';
                foreach ($dataforms as $form) {
                    if ((int)$form['id']===$sourceId) $sourceSlug=(string)$form['slug'];
                    if ((int)$form['id']===$targetId) $targetSlug=(string)$form['slug'];
                }
                if ($sourceSlug==='' || $targetSlug==='') {
                    throw new RuntimeException('Ein ausgewähltes DataForm existiert nicht.');
                }
                $junction='rel_'.str_replace('-','_',$sourceSlug.'_'.$targetSlug);
                $stateStmt=$pdo->prepare(
                    'SELECT is_enabled,configuration_json FROM dataform_relations WHERE id=? LIMIT 1'
                );
                $stateStmt->execute([$id]);
                $state=$stateStmt->fetch(PDO::FETCH_ASSOC)?:[];
                $stateCfg=[];
                if (isset($state['configuration_json']) && is_string($state['configuration_json'])) {
                    $decoded=json_decode($state['configuration_json'],true);
                    if (is_array($decoded)) $stateCfg=$decoded;
                }
                $nextEnabled=!empty($stateCfg['invalid_auto_disabled_hf37'])
                    ? 1
                    : (int)($state['is_enabled']??1);
                $pdo->prepare(
                    'UPDATE dataform_relations
                     SET name=?,relation_type=?,source_dataform_id=?,target_dataform_id=?,
                         source_field_id=NULL,target_display_field_id=NULL,lookup_field_id=NULL,junction_name=?,
                         is_required=?,is_enabled=?,configuration_json=?
                     WHERE id=?'
                )->execute([
                    $name,'n:m',$sourceId,$targetId,$junction,$required?1:0,$nextEnabled,
                    json_encode(['semantics'=>'many-to-many-v1'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                    $id,
                ]);
                $success='Die n:m-Beziehung wurde aktualisiert.';
            }
            $editId=0;
        } elseif ($action==='toggle_relation') {
            $id=(int)($_POST['id']??0);
            $pdo->prepare(
                'UPDATE dataform_relations
                 SET is_enabled=IF(is_enabled=1,0,1)
                 WHERE id=?'
            )->execute([$id]);
            $success='Der Beziehungsstatus wurde geändert.';
        } elseif ($action==='delete_relation') {
            $id=(int)($_POST['id']??0);

            $stmt=$pdo->prepare(
                'SELECT lookup_field_id,configuration_json
                 FROM dataform_relations
                 WHERE id=?'
            );
            $stmt->execute([$id]);
            $relationToDelete=$stmt->fetch()?:[];
            $lookup=(int)(
                $relationToDelete['lookup_field_id']??0
            );
            $cfg=[];
            if (
                isset($relationToDelete['configuration_json'])
                && is_string(
                    $relationToDelete['configuration_json']
                )
            ) {
                $decoded=json_decode(
                    $relationToDelete['configuration_json'],
                    true
                );
                if (is_array($decoded)) {
                    $cfg=$decoded;
                }
            }

            $pdo->beginTransaction();
            try {
                $pdo->prepare(
                    'DELETE FROM dataform_relations WHERE id=?'
                )->execute([$id]);

                // Only remove a field that DataForm itself generated.
                if (
                    $lookup>0
                    && !empty($cfg['auto_form'])
                    && empty($cfg['auto_physical'])
                ) {
                    $pdo->prepare(
                        'DELETE FROM dataform_fields
                         WHERE id=? AND field_type=?'
                    )->execute([$lookup,'lookup']);
                }

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }

            $success='Die Beziehung wurde gelöscht.';
        }
    }

    $relations=$pdo->query(
        "SELECT
            r.*,
            s.name AS parent_name,
            t.name AS child_name,
            pf.label AS parent_display_label,
            pf.name AS parent_display_name,
            lf.label AS child_fk_label,
            lf.name AS child_fk_name,
            sf.label AS source_field_label,
            sf.name AS source_field_name
         FROM dataform_relations r
         JOIN dataforms s
           ON s.id=r.source_dataform_id
         JOIN dataforms t
           ON t.id=r.target_dataform_id
         LEFT JOIN dataform_fields pf
           ON pf.id=r.target_display_field_id
         LEFT JOIN dataform_fields lf
           ON lf.id=r.lookup_field_id
         LEFT JOIN dataform_fields sf
           ON sf.id=r.source_field_id
         ORDER BY r.name"
    )->fetchAll();

    foreach ($relations as &$relation) {
        $relation['_health']=RelationManager::relationHealth($pdo,$relation);
        $relation['_base_lookup']=RelationManager::baseTableLookupDescriptor($relation);
        if (is_array($relation['_base_lookup'])) {
            $relation['child_name']='Basistabelle → '.$relation['_base_lookup']['table'];
        }
        if ($editId>0 && (int)$relation['id']===$editId) {
            $editRelation=$relation;
        }
    }
    unset($relation);

    if ($editId>0 && $editRelation===null) {
        $error='Die ausgewählte Beziehung wurde nicht gefunden.';
        $editId=0;
    }

} catch (Throwable $e) {
    $error=$e->getMessage();
}

$isEditing=is_array($editRelation);
$formName=$isEditing?(string)$editRelation['name']:'';
$formType=$isEditing?(string)$editRelation['relation_type']:'1:n';
$formParentId=$isEditing?(int)$editRelation['source_dataform_id']:$dataformId;
$formChildId=$isEditing?(int)$editRelation['target_dataform_id']:0;
$formDisplayId=$isEditing?(int)($editRelation['target_display_field_id']??0):0;
$formChildFkId=$isEditing?(int)($editRelation['lookup_field_id']??0):0;
$formLookupSourceFieldId=$isEditing?(int)($editRelation['source_field_id']??0):0;
$formLookupDisplayFieldId=$isEditing && $formType==='n:1'
    ? (int)($editRelation['target_display_field_id']??0)
    : 0;
$formRequired=$isEditing && (int)($editRelation['is_required']??0)===1;
$formRelationConfig=$isEditing?RelationManager::relationConfiguration($editRelation):[];
$formBoundFieldReadonly=!$isEditing
    || !array_key_exists('bound_field_readonly',$formRelationConfig)
    || !empty($formRelationConfig['bound_field_readonly']);
$formHealth=$isEditing?(array)($editRelation['_health']??['valid'=>true,'message'=>'']):['valid'=>true,'message'=>''];
$formBaseLookup=$isEditing?RelationManager::baseTableLookupDescriptor($editRelation):null;
$formLookupSourceKind=is_array($formBaseLookup)?'base_table':'dataform';
$formLookupTable=is_array($formBaseLookup)?(string)$formBaseLookup['table']:'';
$formLookupKeyColumn=is_array($formBaseLookup)?(string)$formBaseLookup['key_column']:'id';
$formLookupDisplayColumn=is_array($formBaseLookup)?(string)$formBaseLookup['display_column']:'';

ob_start();
?>
<div class="workspace-breadcrumbs"><?php render_breadcrumbs([
    ['label'=>'Enterprise','href'=>'../../app/dashboard.php'],
    ['label'=>'Projekte','href'=>'../../app/projects/index.php'],
    [
        'label'=>(string)($project['name']??'DataForm'),
        'href'=>$project
            ? '../../app/projects/view.php?id='.(int)$project['id']
            : '',
    ],
    [
        'label'=>'DataForm Workspace',
        'href'=>'index.php?project='.$projectId,
    ],
    ['label'=>'Beziehungen','href'=>''],
]); ?></div>

<div class="df-workspace">
<header class="df-workspace-header">
    <div>
        <span class="badge">DataForm Workspace</span><?php /* Compatibility marker: DataForm Workspace · HF69 */ ?><?php /* Compatibility marker: DataForm Workspace · HF67 */ ?><?php /* Compatibility marker: DataForm Workspace · HF66 */ ?><?php /* Compatibility marker: DataForm Workspace · HF65 */ ?><?php /* Compatibility markers: DataForm Workspace · HF43; DataForm Workspace · HF39 */ ?>
        <h1>Beziehungsdesigner</h1>
        <p>1:n-, n:1/Lookup- und n:m-Beziehungen eindeutig modellieren.</p>
    </div>
    <div class="actions">
        <a
            class="button secondary"
            href="index.php?project=<?= $projectId ?>"
        >Zum Workspace</a>
    </div>
</header>
<?php if($dataformId>0): ?><?= dataform_config_map($projectId,$dataformId,'relations') ?><?= dataform_config_related($projectId,$dataformId,'relations') ?><?php endif; ?>

<?php if($error): ?>
<div class="notice error"><?= e($error) ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="notice success"><?= e($success) ?></div>
<?php endif; ?>
<?php if($info): ?>
<div class="notice info"><?= e($info) ?></div>
<?php endif; ?>

<div class="df-workspace-grid">
<aside class="df-explorer">
    <div class="df-pane-title">Explorer</div>
    <nav>
        <a href="index.php?project=<?= $projectId ?>">Übersicht</a>
        <a href="index.php?project=<?= $projectId ?>&section=dataforms">DataForms</a>
        <a class="active" href="relations.php?project=<?= $projectId ?>">Beziehungen</a>
    </nav>
</aside>

<main class="df-main">
<section class="card">
    <h2><?= $isEditing ? 'Beziehung bearbeiten' : 'Neue Beziehung' ?></h2>

    <?php if($isEditing && empty($formHealth['valid'])): ?>
    <div class="notice error">
        <strong>Die bisherige Zuordnung ist ungültig.</strong>
        <?= e((string)($formHealth['message']??'')) ?>
        Wählen Sie unten ein tatsächlich vorhandenes Zuordnungsfeld.
    </div>
    <?php endif; ?>

    <?php if(count($dataforms)<1): ?>
    <div class="notice warning">
        Für eine Beziehung wird mindestens ein Ausgangs-DataForm benötigt.
    </div>
    <?php else: ?>

    <form
        method="post"
        action="relations.php?project=<?= $projectId ?><?= $dataformId>0?'&amp;dataform='.(int)$dataformId:'' ?>"
        class="form-grid"
        id="relation-form"
    >
        <input type="hidden" name="csrf" value="<?= e(enterprise_csrf()) ?>">
        <input
            type="hidden"
            name="action"
            value="<?= $isEditing ? 'update_relation' : 'create_relation' ?>"
        >
        <input type="hidden" name="project" value="<?= $projectId ?>">
        <?php if($isEditing): ?>
        <input type="hidden" name="id" value="<?= (int)$editRelation['id'] ?>">
        <?php endif; ?>

        <label>
            Beziehungsname
            <input
                name="name"
                required
                value="<?= e($formName) ?>"
                placeholder="z. B. ed_ev_info_typ_to_ed_ev_type"
            >
            <small class="muted">
                Eindeutige Bezeichnung der Beziehung. Der Name beschreibt fachlich, welche DataForms bzw. Felder miteinander verknüpft werden.
            </small>
        </label>

        <label>
            Beziehungstyp / Kardinalität
            <select name="relation_type" id="relation-type">
                <option value="1:n" <?= $formType==='1:n'?'selected':'' ?>>
                    1:n – ein Eltern-Datensatz hat viele Kinder
                </option>
                <option value="n:1" <?= $formType==='n:1'?'selected':'' ?>>
                    n:1 / Lookup – ein Datensatz wählt einen Referenzdatensatz
                </option>
                <option value="n:m" <?= $formType==='n:m'?'selected':'' ?>>
                    n:m – viele zu vielen
                </option>
            </select>
            <small class="muted">
                Legt fest, wie die Datensätze fachlich zusammengehören: 1:n für echte Eltern-Kind-Strukturen, n:1/Lookup für ein Auswahlfeld auf einen Referenzdatensatz und n:m für beidseitige Mehrfachzuordnungen.
            </small>
        </label>

        <label data-many-to-one>
            Lookup-Quelle – woher kommen die auswählbaren Referenzdatensätze?
            <select name="lookup_source_kind" id="lookup-source-kind">
                <option value="dataform" <?= $formLookupSourceKind==='dataform'?'selected':'' ?>>
                    DataForm – Referenzdaten werden über ein vorhandenes DataForm gelesen
                </option>
                <option value="base_table" <?= $formLookupSourceKind==='base_table'?'selected':'' ?>>
                    Basistabelle – Referenzdaten direkt aus einer Projekttabelle lesen
                </option>
            </select>
            <small class="muted">
                Für reine Stamm-, Typ-, Status- oder Wertetabellen ist „Basistabelle“ ausreichend. Dafür muss kein zusätzliches DataForm angelegt werden.
            </small>
        </label>

        <label>
            <span data-parent-label>Eltern-DataForm</span>
            <select name="source_dataform_id" id="parent-dataform" required>
                <option value="">Bitte wählen</option>
                <?php foreach($dataforms as $form): ?>
                <option
                    value="<?= (int)$form['id'] ?>"
                    data-name="<?= e((string)$form['name']) ?>"
                    <?= $formParentId===(int)$form['id']?'selected':'' ?>
                >
                    <?= e((string)$form['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small class="muted" data-parent-help>
                Der Eltern-Datensatz besitzt die technische ID, die von den zugehörigen Kinddatensätzen gespeichert wird.
            </small>
        </label>

        <label data-target-dataform-wrap>
            <span data-child-label>Kind-DataForm</span>
            <select name="target_dataform_id" id="child-dataform" required>
                <option value="">Bitte wählen</option>
                <?php foreach($dataforms as $form): ?>
                <option
                    value="<?= (int)$form['id'] ?>"
                    data-name="<?= e((string)$form['name']) ?>"
                    <?= $formChildId===(int)$form['id']?'selected':'' ?>
                >
                    <?= e((string)$form['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small class="muted" data-child-help>
                Das Kind-DataForm enthält das Fremdschlüsselfeld, in dem die technische ID des Eltern-Datensatzes gespeichert wird.
            </small>
        </label>

        <label data-one-to-many>
            Anzeigefeld des Eltern-Datensatzes im Kindformular
            <select name="target_display_field_id" id="parent-display-field">
                <option value="">Technische Datensatz-ID verwenden</option>
                <?php foreach($fieldsByForm as $formId=>$fields): ?>
                    <?php foreach($fields as $field): ?>
                    <option
                        data-form="<?= (int)$formId ?>"
                        data-field-name="<?= e((string)$field['name']) ?>"
                        value="<?= (int)$field['id'] ?>"
                        <?= $formDisplayId===(int)$field['id']?'selected':'' ?>
                    >
                        <?= e((string)$field['label']) ?>
                        (<?= e((string)$field['name']) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Bestimmt nur, welcher Wert des Eltern-Datensatzes dem Benutzer im Kindformular angezeigt wird. Gespeichert wird weiterhin immer die technische Eltern-ID.
            </small>
        </label>

        <label data-one-to-many>
            Fremdschlüsselfeld im Kind-DataForm – speichert die Eltern-ID
            <select name="child_fk_field_id" id="child-fk-field">
                <option value="">Bitte vorhandenes Feld wählen</option>
                <?php foreach($fieldsByForm as $formId=>$fields): ?>
                    <?php foreach($fields as $field): ?>
                    <option
                        data-form="<?= (int)$formId ?>"
                        data-field-name="<?= e((string)$field['name']) ?>"
                        value="<?= (int)$field['id'] ?>"
                        <?= $formChildFkId===(int)$field['id']?'selected':'' ?>
                    >
                        <?= e((string)$field['label']) ?>
                        (<?= e((string)$field['name']) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Dieses Feld befindet sich im Kind-DataForm und speichert die technische ID des gewählten Eltern-Datensatzes. Es werden nur tatsächlich speicherbare Felder angeboten; bei tabellengebundenen DataForms muss die physische Spalte existieren.
            </small>
        </label>

        <label class="checkbox-line" data-one-to-many>
            <input
                type="checkbox"
                name="create_lookup"
                value="1"
                id="create-child-lookup"
            >
            <span>
                <strong>Neues Fremdschlüsselfeld im Kind anlegen – physische Spalte im Kind-DataForm erzeugen</strong>
                – nur verwenden, wenn kein bestehendes Feld geeignet ist.
            </span>
        </label>

        <label data-one-to-many id="new-child-fk-name-wrap" hidden>
            Technischer Name des neuen Fremdschlüsselfeldes im Kind-DataForm
            <input
                name="new_child_fk_name"
                id="new-child-fk-name"
                placeholder="z. B. to_ev_id"
                pattern="[A-Za-z_][A-Za-z0-9_]{0,63}"
            >
            <small class="muted">
                Bei einem tabellengebundenen Kind-DataForm wird diese Spalte real in der Kindtabelle angelegt.
            </small>
        </label>

        <label data-many-to-one>
            Zuordnungsfeld im Ausgangs-DataForm – speichert die Referenz-ID
            <select name="lookup_source_field_id" id="lookup-source-field">
                <option value="">Bitte vorhandenes Feld wählen</option>
                <?php foreach($fieldsByForm as $formId=>$fields): ?>
                    <?php foreach($fields as $field): ?>
                    <option
                        data-form="<?= (int)$formId ?>"
                        data-field-name="<?= e((string)$field['name']) ?>"
                        value="<?= (int)$field['id'] ?>"
                        <?= $formLookupSourceFieldId===(int)$field['id']?'selected':'' ?>
                    >
                        <?= e((string)$field['label']) ?>
                        (<?= e((string)$field['name']) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Hier wählst du das bereits vorhandene Feld im Ausgangs-DataForm, in dem die technische ID des ausgewählten Referenzdatensatzes gespeichert wird. Beispiel: <code>ed_ev_info.typ</code> speichert <code>ed_ev_type.id</code>.
            </small>
        </label>

        <label data-many-to-one data-base-table-lookup>
            Basistabelle – enthält die auswählbaren Referenzdatensätze
            <select name="lookup_table" id="lookup-table">
                <option value="">Bitte Basistabelle wählen</option>
                <?php foreach($baseTables as $baseTable): ?>
                <option
                    value="<?= e((string)$baseTable) ?>"
                    <?= $formLookupTable===(string)$baseTable?'selected':'' ?>
                ><?= e((string)$baseTable) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Die Datensätze dieser physischen Projekttabelle werden direkt als Lookup-Optionen verwendet. Ein DataForm für diese Tabelle ist nicht erforderlich.
            </small>
        </label>

        <label data-many-to-one data-base-table-lookup>
            Schlüsselfeld der Basistabelle – dieser Wert wird im Ausgangsfeld gespeichert
            <select name="lookup_key_column" id="lookup-key-column">
                <option value="">Bitte Schlüsselfeld wählen</option>
                <?php foreach($baseTableColumns as $tableName=>$columns): ?>
                    <?php foreach($columns as $column): ?>
                    <?php $columnName=(string)($column['Field']??''); ?>
                    <option
                        value="<?= e($columnName) ?>"
                        data-table="<?= e((string)$tableName) ?>"
                        <?= $formLookupTable===(string)$tableName && $formLookupKeyColumn===$columnName?'selected':'' ?>
                    >
                        <?= e($columnName) ?> (<?= e((string)($column['Type']??'')) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Typischerweise <code>id</code>. Der ausgewählte Schlüsselwert wird z. B. in <code>ed_ev_info.typ</code> gespeichert.
            </small>
        </label>

        <label data-many-to-one data-base-table-lookup>
            Anzeigefeld der Basistabelle – sichtbarer Text im Auswahlfeld
            <select name="lookup_display_column" id="lookup-display-column">
                <option value="">Schlüsselfeld als Anzeige verwenden</option>
                <?php foreach($baseTableColumns as $tableName=>$columns): ?>
                    <?php foreach($columns as $column): ?>
                    <?php $columnName=(string)($column['Field']??''); ?>
                    <option
                        value="<?= e($columnName) ?>"
                        data-table="<?= e((string)$tableName) ?>"
                        <?= $formLookupTable===(string)$tableName && $formLookupDisplayColumn===$columnName?'selected':'' ?>
                    >
                        <?= e($columnName) ?> (<?= e((string)($column['Type']??'')) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Beispiel: In <code>ed_ev_type</code> ist <code>id</code> der gespeicherte Schlüssel und <code>ev_type</code> die sichtbare Bezeichnung wie „Meeting“.
            </small>
        </label>

        <label data-many-to-one data-dataform-lookup>
            Anzeigefeld des Referenzdatensatzes – sichtbarer Wert im Auswahlfeld
            <select name="lookup_display_field_id" id="lookup-display-field">
                <option value="">Technische Datensatz-ID verwenden</option>
                <?php foreach($fieldsByForm as $formId=>$fields): ?>
                    <?php foreach($fields as $field): ?>
                    <option
                        data-form="<?= (int)$formId ?>"
                        data-field-name="<?= e((string)$field['name']) ?>"
                        value="<?= (int)$field['id'] ?>"
                        <?= $formLookupDisplayFieldId===(int)$field['id']?'selected':'' ?>
                    >
                        <?= e((string)$field['label']) ?>
                        (<?= e((string)$field['name']) ?>)
                    </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
            <small class="muted">
                Dieses Feld wird dem Benutzer im Auswahlfeld angezeigt, z. B. <code>ev_type = Meeting</code>. Es ist nur die sichtbare Bezeichnung; gespeichert wird die technische ID des Referenzdatensatzes.
            </small>
        </label>

        <div class="notice info full" data-many-to-one data-dataform-lookup>
            <strong>Technischer Referenzschlüssel:</strong>
            Das Lookup-/Referenz-DataForm wird immer über seine technische Datensatz-ID <code>id</code> referenziert. Dieses Zielfeld ist fest und muss nicht ausgewählt werden.
        </div>

        <div class="notice info full" data-many-to-one>
            <strong>n:1-/Lookup-Semantik:</strong>
            <code id="lookup-source-key">Ausgang.&lt;Zuordnungsfeld&gt;</code>
            →
            <code id="lookup-target-key">Lookup.id</code>.
            <span id="lookup-semantics-help">Im Ausgangsformular erscheint dafür ein Auswahlfeld; gespeichert wird der Schlüsselwert des Lookup-Datensatzes.</span>
        </div>

        <label class="checkbox-line" data-one-to-many>
            <input
                type="checkbox"
                name="bound_field_readonly"
                value="1"
                <?= $formBoundFieldReadonly?'checked':'' ?>
            >
            <span>
                <strong>Gebundenes Fremdschlüsselfeld schreibgeschützt</strong>
                – Voreinstellung: Ja. Der aktuelle Elternwert wird im Kind automatisch vorbelegt. Ist diese Option deaktiviert, bleibt die Eltern-ID die Anfangsbelegung, kann aber bewusst geändert werden.
            </span>
        </label>

        <label class="checkbox-line">
            <input
                type="checkbox"
                name="is_required"
                value="1"
                <?= $formRequired?'checked':'' ?>
            >
            <span data-required-label>Zuordnung ist erforderlich – Datensatz darf nicht ohne gültige Beziehung gespeichert werden</span>
        </label>

        <div class="notice info full" data-one-to-many>
            <strong>1:n-Semantik:</strong>
            <code id="relation-parent-key">Eltern.id</code>
            →
            <code id="relation-child-key">Kind.&lt;Fremdschlüsselfeld&gt;</code>.
            Das Elternformular selbst erhält kein Eltern-Lookup.
        </div>

        <p class="actions">
            <button
                class="button"
                type="submit"
                data-crud="<?= $isEditing?'edit':'create' ?>"
            >
                <?= $isEditing ? 'Änderungen speichern' : 'Beziehung anlegen' ?>
            </button>
            <?php if($isEditing): ?>
            <a
                class="button secondary"
                href="relations.php?project=<?= $projectId ?>"
            >Abbrechen</a>
            <?php endif; ?>
        </p>
    </form>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Vorhandene Beziehungen</h2>

    <?php if(!$relations): ?>
    <p class="muted">Noch keine Beziehungen vorhanden.</p>
    <?php else: ?>

    <div class="df-field-table-wrap">
    <table class="df-field-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Typ</th>
            <th>DataForm / Seite A</th>
            <th>Ziel / Seite B</th>
            <th>Zuordnung</th>
            <th>Status</th>
            <th>Aktionen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($relations as $relation): ?>
        <tr>
            <td><?= e((string)$relation['name']) ?></td>
            <td>
                <span class="badge">
                    <?= e((string)$relation['relation_type']) ?>
                </span>
            </td>
            <td><?= e((string)$relation['parent_name']) ?></td>
            <td><?= e((string)$relation['child_name']) ?></td>
            <td>
                <?php if($relation['relation_type']==='n:m'): ?>
                    <?= e((string)$relation['junction_name']) ?>
                <?php elseif($relation['relation_type']==='n:1'): ?>
                    <code><?= e((string)($relation['source_field_name']?:'nicht zugeordnet')) ?></code>
                    →
                    <?php $baseLookup=$relation['_base_lookup']??null; ?>
                    <?php if(is_array($baseLookup)): ?>
                        <code><?= e((string)$baseLookup['table']) ?>.<?= e((string)$baseLookup['key_column']) ?></code>
                        <br><span class="muted">
                            Lookup-Quelle: Basistabelle
                            <?php if((string)($baseLookup['display_column']??'')!==''): ?>
                                · Anzeige: <?= e((string)$baseLookup['display_column']) ?>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <code>id</code>
                        <?php if($relation['parent_display_label']): ?>
                        <br><span class="muted">
                            Anzeige Ziel:
                            <?= e((string)$relation['parent_display_label']) ?>
                        </span>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <code>id</code>
                    →
                    <code><?= e(
                        (string)(
                            $relation['child_fk_name']
                            ?: 'nicht zugeordnet'
                        )
                    ) ?></code>
                    <?php if($relation['parent_display_label']): ?>
                    <br>
                    <span class="muted">
                        Anzeige:
                        <?= e(
                            (string)$relation['parent_display_label']
                        ) ?>
                    </span>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php $health=(array)($relation['_health']??['valid'=>true,'message'=>'']); ?>
                <?php if(empty($health['valid'])): ?>
                    <strong class="df-relation-invalid">fehlerhaft</strong>
                    <br><span class="muted"><?= e((string)($health['message']??'')) ?></span>
                <?php else: ?>
                    <?= (int)$relation['is_enabled']===1 ? 'aktiv' : 'inaktiv' ?>
                <?php endif; ?>
            </td>
            <td>
                <a
                    class="button secondary"
                    data-crud="edit"
                    href="relations.php?project=<?= $projectId ?>&edit=<?= (int)$relation['id'] ?>#relation-form"
                >Bearbeiten</a>

                <form method="post" style="display:inline">
                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= e(enterprise_csrf()) ?>"
                    >
                    <input
                        type="hidden"
                        name="project"
                        value="<?= $projectId ?>"
                    >
                    <input
                        type="hidden"
                        name="action"
                        value="toggle_relation"
                    >
                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$relation['id'] ?>"
                    >
                    <button
                        class="button"
                        <?= easyit_button_attributes((int)$relation['is_enabled']===1?'sperren':'entsperren') ?>
                        type="submit"
                    >
                        <?= (int)$relation['is_enabled']===1
                            ? 'Deaktivieren'
                            : 'Aktivieren' ?>
                    </button>
                </form>

                <form
                    method="post"
                    style="display:inline"
                    onsubmit="return confirm('Beziehung wirklich löschen? Ein vorhandenes normales Fremdschlüsselfeld im Kind bleibt erhalten.');"
                >
                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= e(enterprise_csrf()) ?>"
                    >
                    <input
                        type="hidden"
                        name="project"
                        value="<?= $projectId ?>"
                    >
                    <input
                        type="hidden"
                        name="action"
                        value="delete_relation"
                    >
                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$relation['id'] ?>"
                    >
                    <button
                        class="button"
                        <?= easyit_button_attributes('beziehung_loeschen') ?>
                        type="submit"
                    >Löschen</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</section>
</main>

<aside class="df-properties">
    <div class="df-pane-title">Kontexthilfe</div>
    <h3>1:n</h3>
    <p>
        Der Eltern-Datensatz besitzt die technische ID.
        Das Kind speichert diese ID in einem tatsächlich vorhandenen Fremdschlüsselfeld,
        zum Beispiel <code>to_ev_id</code>. Bei tabellengebundenen DataForms muss
        dieses Feld als physische Spalte in der Kindtabelle existieren.
    </p>
    <h3>Wichtig</h3>
    <p>
        Das Elternformular zeigt deshalb kein
        „Eltern-Datensatz wählen“. Die Auswahl erscheint ausschließlich
        im Kindformular.
    </p>
    <h3>n:1 / Lookup</h3>
    <p>
        <strong>Ausgangs-DataForm</strong> ist das Formular, in dem der Benutzer eine Auswahl trifft.
        Das <strong>Zuordnungsfeld</strong> liegt in diesem DataForm und speichert die technische ID.
        Als <strong>Lookup-Quelle</strong> kann entweder ein vorhandenes Referenz-DataForm oder direkt eine
        <strong>Basistabelle</strong> verwendet werden. Bei einer Basistabelle werden Schlüssel- und Anzeigespalte
        direkt ausgewählt; ein zusätzliches DataForm ist dafür nicht erforderlich. Beispiel:
        <code>ed_ev_info.typ → ed_ev_type.id</code>; angezeigt wird etwa <code>ed_ev_type.ev_type = Meeting</code>.
    </p>
    <h3>n:m</h3>
    <p>
        Für n:m bleiben beide Seiten gleichrangig und werden über eine
        Zuordnung verbunden.
    </p>
</aside>
</div>
</div>

<script>
(function(){
    var type=document.getElementById('relation-type');
    var parent=document.getElementById('parent-dataform');
    var child=document.getElementById('child-dataform');
    var display=document.getElementById('parent-display-field');
    var fk=document.getElementById('child-fk-field');
    var lookupSource=document.getElementById('lookup-source-field');
    var lookupDisplay=document.getElementById('lookup-display-field');
    var lookupSourceKind=document.getElementById('lookup-source-kind');
    var lookupTable=document.getElementById('lookup-table');
    var lookupKeyColumn=document.getElementById('lookup-key-column');
    var lookupDisplayColumn=document.getElementById('lookup-display-column');
    var targetDataformWrap=document.querySelector('[data-target-dataform-wrap]');
    var lookupSemanticsHelp=document.getElementById('lookup-semantics-help');
    var createLookup=document.getElementById('create-child-lookup');
    var newFieldWrap=document.getElementById('new-child-fk-name-wrap');
    var newField=document.getElementById('new-child-fk-name');
    var parentKey=document.getElementById('relation-parent-key');
    var childKey=document.getElementById('relation-child-key');
    var lookupSourceKey=document.getElementById('lookup-source-key');
    var lookupTargetKey=document.getElementById('lookup-target-key');
    var parentLabel=document.querySelector('[data-parent-label]');
    var childLabel=document.querySelector('[data-child-label]');
    var parentHelp=document.querySelector('[data-parent-help]');
    var childHelp=document.querySelector('[data-child-help]');
    var requiredLabel=document.querySelector('[data-required-label]');

    function filterSelect(select,formId){
        if(!select) return;
        Array.from(select.options).forEach(function(option,index){
            if(index===0){
                option.hidden=false;
                option.disabled=false;
                return;
            }
            var allowed=option.dataset.form===formId;
            option.hidden=!allowed;
            option.disabled=!allowed;
        });
        if(select.selectedOptions.length && select.selectedOptions[0].disabled){
            select.value='';
        }
    }


    function filterTableColumnSelect(select,tableName){
        if(!select) return;
        Array.from(select.options).forEach(function(option,index){
            if(index===0){
                option.hidden=false;
                option.disabled=false;
                return;
            }
            var allowed=option.dataset.table===tableName;
            option.hidden=!allowed;
            option.disabled=!allowed;
        });
        if(select.selectedOptions.length && select.selectedOptions[0].disabled){
            select.value='';
        }
    }

    function selectedName(select,fallback){
        if(!select || !select.selectedOptions.length) return fallback;
        return select.selectedOptions[0].dataset.name || fallback;
    }

    function selectedFieldName(select,fallback){
        if(!select || !select.selectedOptions.length || select.value==='') return fallback;
        return select.selectedOptions[0].dataset.fieldName || fallback;
    }

    function syncSemantics(){
        var sourceName=selectedName(parent,'Ausgang');
        var targetName=selectedName(child,'Ziel');

        if(parentKey && childKey){
            var fieldName='<Fremdschlüsselfeld>';
            if(createLookup && createLookup.checked){
                fieldName=(newField && newField.value.trim()!=='')
                    ? newField.value.trim()
                    : '<neues Feld>';
            }else{
                fieldName=selectedFieldName(fk,'<Fremdschlüsselfeld>');
            }
            parentKey.textContent=sourceName+'.id';
            childKey.textContent=targetName+'.'+fieldName;
        }

        if(lookupSourceKey && lookupTargetKey){
            var sourceKind=lookupSourceKind?lookupSourceKind.value:'dataform';
            lookupSourceKey.textContent=sourceName+'.'+selectedFieldName(lookupSource,'<Zuordnungsfeld>');
            if(sourceKind==='base_table'){
                var tableName=(lookupTable && lookupTable.value!=='')?lookupTable.value:'<Basistabelle>';
                var keyName=(lookupKeyColumn && lookupKeyColumn.value!=='')?lookupKeyColumn.value:'<Schlüsselfeld>';
                lookupTargetKey.textContent=tableName+'.'+keyName;
                if(lookupSemanticsHelp){
                    var displayName=(lookupDisplayColumn && lookupDisplayColumn.value!=='')
                        ? lookupDisplayColumn.value
                        : keyName;
                    lookupSemanticsHelp.textContent='Die Lookup-Optionen werden direkt aus der Basistabelle gelesen. Gespeichert wird '+keyName+'; angezeigt wird '+displayName+'. Ein Referenz-DataForm ist nicht erforderlich.';
                }
            }else{
                lookupTargetKey.textContent=targetName+'.id';
                if(lookupSemanticsHelp){
                    lookupSemanticsHelp.textContent='Im Ausgangsformular erscheint dafür ein Auswahlfeld; gespeichert wird die technische ID des Lookup-DataForm-Datensatzes.';
                }
            }
        }
    }

    function syncForms(){
        var relationType=type?type.value:'1:n';
        filterSelect(display,parent?parent.value:'');
        filterSelect(fk,child?child.value:'');
        filterSelect(lookupSource,parent?parent.value:'');
        filterSelect(lookupDisplay,child?child.value:'');
        if(parentLabel){
            parentLabel.textContent=relationType==='1:n'
                ? 'Eltern-DataForm – besitzt die Kinddatensätze'
                : (relationType==='n:1'
                    ? 'Ausgangs-DataForm – hier erfolgt die Auswahl'
                    : 'DataForm / Seite A');
        }
        if(childLabel){
            childLabel.textContent=relationType==='1:n'
                ? 'Kind-DataForm – speichert die Eltern-ID'
                : (relationType==='n:1'
                    ? 'Lookup-/Referenz-DataForm – liefert die auswählbaren Datensätze'
                    : 'DataForm / Seite B');
        }
        if(parentHelp){
            parentHelp.textContent=relationType==='1:n'
                ? 'Ein Datensatz dieses DataForms kann mehrere Datensätze des Kind-DataForms besitzen. Seine technische ID wird im Fremdschlüsselfeld des Kindes gespeichert.'
                : (relationType==='n:1'
                    ? 'Dieses DataForm wird bearbeitet. In einem seiner Felder wird die technische ID des ausgewählten Referenzdatensatzes gespeichert.'
                    : 'Erste Seite der n:m-Zuordnung.');
        }
        if(childHelp){
            childHelp.textContent=relationType==='1:n'
                ? 'Dieses DataForm enthält die untergeordneten Datensätze und das Feld, das die technische ID des Eltern-Datensatzes speichert.'
                : (relationType==='n:1'
                    ? 'Aus diesem DataForm stammen die Datensätze, die der Benutzer im Ausgangsformular auswählen kann. Referenziert wird immer dessen technische ID.'
                    : 'Zweite Seite der n:m-Zuordnung.');
        }
        if(requiredLabel){
            requiredLabel.textContent=relationType==='1:n'
                ? 'Eltern-Zuordnung ist Pflicht – jeder Kinddatensatz muss einem Eltern-Datensatz zugeordnet sein'
                : (relationType==='n:1'
                    ? 'Lookup-Auswahl ist Pflicht – der Ausgangsdatensatz muss einen gültigen Referenzdatensatz auswählen'
                    : 'Beziehung ist erforderlich');
        }
        syncSemantics();
    }


    function syncLookupSource(){
        var relationType=type?type.value:'1:n';
        var manyToOne=relationType==='n:1';
        var sourceKind=lookupSourceKind?lookupSourceKind.value:'dataform';
        var baseMode=manyToOne && sourceKind==='base_table';
        var dataformMode=manyToOne && sourceKind==='dataform';

        document.querySelectorAll('[data-base-table-lookup]').forEach(function(element){
            element.hidden=!baseMode;
        });
        document.querySelectorAll('[data-dataform-lookup]').forEach(function(element){
            element.hidden=!dataformMode;
        });

        if(targetDataformWrap){
            targetDataformWrap.hidden=baseMode;
        }
        if(child){
            child.required=relationType==='1:n' || relationType==='n:m' || dataformMode;
        }
        if(lookupTable){
            lookupTable.required=baseMode;
        }
        if(lookupKeyColumn){
            lookupKeyColumn.required=baseMode;
        }
        if(lookupDisplay){
            lookupDisplay.required=false;
        }

        var tableName=lookupTable?lookupTable.value:'';
        filterTableColumnSelect(lookupKeyColumn,tableName);
        filterTableColumnSelect(lookupDisplayColumn,tableName);

        if(baseMode && lookupKeyColumn && lookupKeyColumn.value===''){
            var idOption=Array.from(lookupKeyColumn.options).find(function(option){
                return !option.disabled && option.value.toLowerCase()==='id';
            });
            if(idOption) lookupKeyColumn.value=idOption.value;
        }

        syncSemantics();
    }

    function syncType(){
        var relationType=type?type.value:'1:n';
        var oneToMany=relationType==='1:n';
        var manyToOne=relationType==='n:1';

        document.querySelectorAll('[data-one-to-many]').forEach(function(element){
            element.hidden=!oneToMany;
        });
        document.querySelectorAll('[data-many-to-one]').forEach(function(element){
            element.hidden=!manyToOne;
        });

        if(newField){
            newField.required=oneToMany && !!(createLookup && createLookup.checked);
        }
        if(lookupSource){
            lookupSource.required=manyToOne;
        }
        if(lookupDisplay){
            lookupDisplay.required=false;
        }
        syncForms();
        syncLookupSource();
    }

    function syncNewField(){
        var oneToMany=!type || type.value==='1:n';
        var creating=oneToMany && !!(createLookup && createLookup.checked);
        if(newFieldWrap) newFieldWrap.hidden=!creating;
        if(newField) newField.required=creating;
        if(creating && fk) fk.value='';
        syncSemantics();
    }

    if(parent) parent.addEventListener('change',syncForms);
    if(child) child.addEventListener('change',syncForms);
    if(type) type.addEventListener('change',function(){
        syncType();
        syncNewField();
    });
    if(fk){
        fk.addEventListener('change',function(){
            if(fk.value!=='' && createLookup){
                createLookup.checked=false;
                syncNewField();
            }
            syncSemantics();
        });
    }
    if(lookupSource) lookupSource.addEventListener('change',syncSemantics);
    if(lookupDisplay) lookupDisplay.addEventListener('change',syncSemantics);
    if(lookupSourceKind) lookupSourceKind.addEventListener('change',syncLookupSource);
    if(lookupTable) lookupTable.addEventListener('change',syncLookupSource);
    if(lookupKeyColumn) lookupKeyColumn.addEventListener('change',syncSemantics);
    if(lookupDisplayColumn) lookupDisplayColumn.addEventListener('change',syncSemantics);
    if(createLookup) createLookup.addEventListener('change',syncNewField);
    if(newField) newField.addEventListener('input',syncSemantics);

    syncType();
    syncLookupSource();
    syncNewField();
})();
</script>
<?php
$content=ob_get_clean();

render_page([
    'title'=>'Beziehungen',
    'active'=>'projects',
    'base'=>'../../',
    'content'=>$content,
    'app_nav'=>true,
    'user'=>$user,
    'body_class'=>'workspace-page',
    'styles'=>[
        'products/dataform/assets/workspace.css',
    ],
    'help'=>[
        'title'=>'Beziehungsdesigner',
        'location'=>'DataForm → Beziehungen',
        'goal'=>'1:n, n:1/Lookup (DataForm oder Basistabelle) und n:m passend zur fachlichen Datenstruktur definieren.',
        'next'=>'Beziehung speichern und die Auswahl direkt im betroffenen DataForm testen.',
        'duration'=>'ca. 3 Minuten',
    ],
]);

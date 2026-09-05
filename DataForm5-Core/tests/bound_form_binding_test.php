<?php
declare(strict_types=1);

use EasyIT\DataForm5\Binding\BindingDefinition;
use EasyIT\DataForm5\Binding\BoundFormBindingService;

require_once dirname(__DIR__)
    . '/system/dataform/binding/BindingDefinition.php';

require_once dirname(__DIR__)
    . '/system/dataform/binding/BoundFormBindingService.php';

$tests = 0;
$passed = 0;

function check(
    bool $condition,
    string $message
): void {
    global $tests, $passed;

    $tests++;

    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    $passed++;
}

$service = new BoundFormBindingService();

$readonly = new BindingDefinition(
    parentRecordsetId: 'rs_ed_ev',
    parentField: 'id',
    childRecordsetId: 'rs_ed_ev_info',
    childField: 'to_ev_id',
    inheritParentValue: true,
    boundFieldReadonly: true,
    relationId: 'ed_ev__ed_ev_info'
);

$editable = new BindingDefinition(
    parentRecordsetId: 'rs_ed_ev',
    parentField: 'id',
    childRecordsetId: 'rs_ed_ev_info',
    childField: 'to_ev_id',
    inheritParentValue: true,
    boundFieldReadonly: false,
    relationId: 'ed_ev__ed_ev_info'
);

$parent9201 = ['id' => 9201];

// 1
$defaults = $service->applyNewRecordDefaults(
    $readonly,
    $parent9201,
    ['typ' => 'Nachricht']
);
check(
    $defaults['to_ev_id'] === 9201,
    'Neuer Child muss Parent-ID 9201 übernehmen.'
);

// 2
check(
    $defaults['typ'] === 'Nachricht',
    'Andere Defaults müssen erhalten bleiben.'
);

// 3
$submitted = $service->enforceBeforePersist(
    $readonly,
    $parent9201,
    [
        'to_ev_id' => 9999,
        'typ' => 'Nachricht',
    ],
    true
);
check(
    $submitted['to_ev_id'] === 9201,
    'Read-only muss manipulierten Request auf Parent-ID zurücksetzen.'
);

// 4
$submittedEditable = $service->enforceBeforePersist(
    $editable,
    $parent9201,
    [
        'to_ev_id' => 9300,
        'typ' => 'Gespräch',
    ],
    true
);
check(
    $submittedEditable['to_ev_id'] === 9300,
    'Editierbare Bindung muss bewusst geänderten Wert erhalten.'
);

// 5
$submittedNoField = $service->enforceBeforePersist(
    $editable,
    $parent9201,
    [
        'typ' => 'Gespräch',
    ],
    true
);
check(
    $submittedNoField['to_ev_id'] === 9201,
    'Editierbarer Insert ohne Feldwert muss Parent-ID vorbelegen.'
);

// 6
check(
    $service->canCreateChild(
        $readonly,
        ['id' => null]
    ) === false,
    'Ohne Parent-ID darf kein Child angelegt werden.'
);

// 7
check(
    $service->canCreateChild(
        $readonly,
        $parent9201
    ) === true,
    'Mit Parent-ID muss Child-Anlage möglich sein.'
);

// 8
$filter = $service->buildChildFilter(
    $readonly,
    $parent9201
);
check(
    $filter === ['to_ev_id' => 9201],
    'Child-Filter muss auf Parent-ID zeigen.'
);

// 9
$context = $service->buildContextParent(
    $readonly,
    $parent9201,
    'ed_ev'
);
check(
    $context['binding']['inherited'] === true
    && $context['binding']['readonly'] === true
    && $context['keyValue'] === 9201,
    'dataformContext-Bindungsinformation ist falsch.'
);

// 10
$exceptionThrown = false;
try {
    $service->applyNewRecordDefaults(
        $readonly,
        ['id' => null]
    );
} catch (DomainException $e) {
    $exceptionThrown = true;
}
check(
    $exceptionThrown,
    'Neuer Child ohne gespeicherten Parent muss blockiert werden.'
);


// 11
$fieldState = $service->buildBoundFieldState(
    $readonly,
    $parent9201
);
check(
    $fieldState['value'] === 9201
    && $fieldState['displayValue'] === '#9201'
    && $fieldState['readonly'] === true,
    'Neue gebundene Zeile muss sichtbar #9201 erhalten.'
);

// 12
$fieldStateEditable = $service->buildBoundFieldState(
    $editable,
    $parent9201
);
check(
    $fieldStateEditable['value'] === 9201
    && $fieldStateEditable['readonly'] === false,
    'Editierbare Bindung muss Parent-ID vorbelegen, aber editierbar bleiben.'
);

echo "PASS: {$passed}/{$tests}\n";

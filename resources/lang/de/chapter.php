<?php

return [
    'common' => [

    ],
    'frontend' => [

    ],
    'backend' => [
        'actions' => [
            'create chapter' => 'Kapitel erstellen',
            'edit chapter' => 'Kapitel bearbeiten',
            'update chapters' => 'Kapitel aktualisieren',
            'delete chapter' => 'Kapitel löschen',
            'update chapter' => 'Kapitel aktualisieren',
            'back to series chapters' => 'Zurück zu Serienkapiteln',
            'add selected clips to chapter' => 'Ausgewählte Clips zum Kapitel  hinzufügen',
            'remove selected clips from chapter' => 'Ausgewählte Clips aus dem Kapitel entfernen',
        ],
        'chapters for series' => 'Kapitel für Serie: :seriesTitle / SerienID: :seriesID',
        'edit chapter for series' => 'Kapitel bearbeiten: :chapterPosition - :chapterTitle 
                                    für Serie: <span class="italic">:seriesTitle </span>',
        'series chapters info text' => 'Markieren Sie, ob ein Kapitel das Standardkapitel sein soll oder nicht. '.
                                        'Es kann nur ein Kapitel als Standard festgelegt werden.',
        'new chapter' => 'Neues Kapitel',
        'chapter title' => 'Kapitel Titel',
        'series chapters' => 'Serienkapitel',
        'no chapters found for' => 'Keine Kapitel gefunden für :seriesTitle',
        'default chapter' => 'Standardkapitel',
        'add clips to chapter' => 'Clips zum Kapitel hinzufügen',
        'clips for this chapter' => 'Clips für dieses Kapitel',
        'delete' => [
            'modal title' => 'Sind Sie sicher, dass Sie das Kapitel ":chapter_title" löschen möchten?',
            'modal body' => 'Diese Aktion wird keine Clips löschen, die mit diesem Kapitel verknüpft sind. '.
                'Wenn dieses Kapitel Clips enthält, werden diese neu geordnet und ohne Kapitelzuordnung zugewiesen.',
        ],
    ],
];

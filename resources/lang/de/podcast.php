<?php

return [
    'common' => [
        'edit podcast' => 'Podcast bearbeiten',
        'no podcast episodes found' => 'Keine Podcast-Episoden gefunden',
        'podcast index' => 'Podcast-Index',
    ],
    'frontend' => [
        // TODO this need to be dynamic
        'jumbotron heading' => 'Podcasts von Studierenden, Lehrstühlen und der Uni-Leitung',
        'jumbotron body' => 'An der FAU gibt es eine ganze Reihe an Podcasts: Studierende stellen Wissenschaftlerinnen 
                            und Wissenschaftler vor, Kanzler Christian Zens spricht über Entwicklungen an der Uni und 
                            einzelne Lehrstühle präsentieren ihre Forschung. Einen Überblick über das Podcast-Angebot 
                            der FAU finden Sie auf dieser Seite.',
        'no podcasts available or published' => 'Keine Podcasts verfügbar oder veröffentlicht.',
        'episode details' => 'Episodendetails',
    ],
    'backend' => [
        'actions' => [
            'create new podcast' => 'Einen neuen Podcast erstellen',
            'add new episode' => 'Neue Episode hinzufügen',
            'edit metadata of multiple episodes' => 'Metadaten mehrerer Episoden bearbeiten',
            'reorder podcast episodes' => 'Podcast-Episoden neu anordnen',
        ],
        'delete' => [
            'modal title' => 'Sind Sie sicher, dass Sie den Podcast mit dem Titel ":podcast_title" löschen möchten?',
            'modal body' => 'Bitte gehen Sie mit Vorsicht vor. Das Löschen dieses Podcasts wird alle zugehörigen'.
                'Episoden, Assets, einschließlich Audiodateien und Transkriptionen dauerhaft entfernen. Sobald gelöscht,
                 wird der Podcast nicht mehr für Benutzer zugänglich oder wiederherstellbar sein.',
        ],
    ],
];

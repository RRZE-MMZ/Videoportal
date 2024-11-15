<?php

return [
    'common' => [
        'episode' => 'Folge',
        'poster' => 'Poster',
        'title' => 'Titel',
        'access via' => 'Zugang via',
        'no clips' => 'Series hat keine Clips',
        'clips without chapter(s)' => 'Clips ohne Kapitel',
        'semester' => 'Semester',
        'duration' => 'Dauer',
        'actions' => 'Aktionen',
        'edit series' => 'Series bearbeiten',
        'my series' => 'Meine Serien',
        'created at' => 'erstellt am',
    ],
    'frontend' => [
        'show' => [
            'views' => ':counter Videoaufrufe',
            'spanning in more semesters with the last one being' => 'Verteilt sich über mehrere Semester, wobei das '
                .'letzte das :semester_title ist',

        ],
        'index' => [
            'Series index' => 'Serienindex',
            'no series' => 'Portal hat noch keine Serie',
        ],
    ],
    'backend' => [
        'actions' => [
            'create series' => 'Neue Serie erstellen',
            'select semester' => 'Bitte wählen Sie das Semester aus',
            'select series' => 'Serie auswählen',
            'reorder series clips' => 'Serienclips neu anordnen',
            'add new clip' => 'Neuen Clip hinzufügen',
            'go to public page' => 'Zur öffentlichen Seite gehen',
            'edit metadata of multiple clips' => 'Metadaten mehrerer Clips bearbeiten',
            'manage chapters' => 'Kapitel verwalten',
            'mass update all clips' => 'Alle Clips massenweise aktualisieren',
            'back to edit series' => 'Zurück zur Bearbeitung der Serie',
        ],
        'Series administrator' => 'Serien Administrator',
        'Set a series owner' => 'Serien-Besitzer einsetzen',
        'Series has no owner yet' => 'Die Serie hat noch keinen Besitzer',
        'Update Series' => 'Serien aktualisieren',
        'update series owner' => 'Serienbesitzer aktualisieren',
        'Add a series member' => 'Neue Serien-Teilnehmer hinzufügen',
        'actual episode' => 'Aktuelle Episode:',
        'no user series found' => 'Sie haben noch keine Serie. Bitte erstellen Sie eine!',
        'mass update clip metadata for series' => 'Massenaktualisierung der Clip-Metadaten für die '.
                            'Serie: <span class="pl-2 font-semibold">:seriesTitle</span>',
        'Series chapter has no clips' => 'Das Kapitel <span class="italic"> :chapterTitle </span> der Serie enthält '.
                                        'keine Clips',
        'Select a series for clip' => 'Wählen Sie eine Serie für den Clip: :clip_title',
        'reorder clips for series' => 'Clips für die Serie neu '.
                                'anordnen:  <span class="pl-2 italic">:series_title</span>',
        'reorder series clips with chapters info' => 'Da deine Serie Kapitel hat, wird die Reihenfolge der Clips auf '.
                            'der Verwaltungsseite der Serienkapitel angepasst.',
        'delete' => [
            'modal title' => 'Sind Sie sicher, dass Sie den Serien „:series_title“ löschen möchten?',
            'modal body' => 'Bitte vorsichtig vorgehen. Das Löschen dieses Serien wird alle zugehörigen Clips sowie '.
                            'die  Ressourcen, einschließlich Videodateien und Transkriptionen, dauerhaft entfernen. '.
                            'Nach dem Löschen ist die Serien für Benutzer nicht mehr zugänglich.',

        ],
    ],
];

<?php

return [
    'common' => [
        'no clips' => 'Keine Clips gefunden',
        'trending clips' => 'Trendende Clips',
    ],
    'frontend' => [
        'index' => [
            'Clips index' => 'Clip-Index',
            'Portal has no clips yet!' => 'Portal hat noch keine Clips',
        ],
        'show' => [
            'Back to clip edit page' => 'Zurück zur Clip-Bearbeitungsseite',
            'presenter video stream' => 'Präsentator-Videostream',
            'presentation video stream' => 'Präsentations-Videostream',
            'composite video stream' => 'Komposit-Videostream',
            'to LMS course' => 'Zum LMS-Kurs',
            'views' => ':numViews Videoaufrufe',
        ],

        'not authorized to view video' => 'Sie sind nicht berechtigt, dieses Video anzusehen!',
        'this clip is exclusively accessible to logged-in users' => 'Dieser Clip ist ausschließlich für angemeldete'.
                                                                     'Benutzer zugänglich.',
        'access to this clip is restricted to LMS course participants' => 'Der Zugang zu diesem Clip ist auf '.
                                                                            'LMS-Kursteilnehmer beschränkt.',
        'this clip requires a password for access' => 'Für diesen Clip ist ein Passwort erforderlich. '.
                                                        'Bitte kontaktieren Sie die Administratoren der Videoserie.',
        'comments' => 'Kommentare',
    ],
    'backend' => [
        'belongs to' => 'Teil von Serien: :series_title',
        'assign a series to this clip' => 'Eine Serie diesem Clip zuweisen',
        'upload a video' => 'Ein Video hochladen',
        'opencast video upload description' => 'Es wird ein neuer Video-Workflow gestartet.
         Vielen Dank für Ihre Geduld.',
        'video files in dropzone' => 'Videodateien in der Dropzone',
        'please select one or more audio/video files' => 'Bitte wählen Sie eine oder mehrere Audio-/Videodateien aus',
        'LMS Test Link' => 'LMS-Testlink',
        'my clips' => 'Meine Clips',
        'with video files' => 'Mit Videoanlagen',
        'actions' => [
            'go to clip public page' => 'Zur öffentlichen Clip-Seite gehen',
            'got to clip statistics page' => 'Zur Clip-Statistikseite gehen',
            'generate preview from frame' => 'Vorschau aus Frame erstellen',
            'upload an image' => 'Ein Bild hochladen',
            're-trigger streaming smil files' => 'Streaming SMIL-Dateien erneut auslösen',
            'transfer files from drop zone' => 'Dateien aus der Dropzone übertragen',
            'upload already transcoded recording' => 'Eine bereits transkodierte Aufnahme hochladen',
            'remove series' => 'Serie entfernen',
            'view available series' => 'Verfügbare Serien anzeigen',
            'upload video' => 'Video hochladen',
            'add selected audio/video files to clip' => 'Ausgewählte Audio-/Videodateien zum Clip hinzufügen',
        ],
        'delete' => [
            'modal title' => 'Sind Sie sicher, dass Sie den Clip „:clip_title“ löschen möchten?',
            'modal body' => 'Bitte vorsichtig vorgehen. Das Löschen dieses Clips wird alle zugehörigen Ressourcen,'.
                            ' einschließlich Videodateien und Transkriptionen, dauerhaft entfernen. Nach dem Löschen '.
                            ' ist der Clip für Benutzer nicht mehr zugänglich.',
        ],
        'delete series connection' => [
            'modal title' => 'Möchten Sie den Clip aus der Serie mit dem Titel ":series_title" entfernen?',
            'modal body' => 'Durch das Entfernen des Clips aus der Serie wird dieser nicht mehr Teil dieser Serie'.
               'sein. Der Clip behält jedoch alle seine Informationen, einschließlich Metadaten, Zugriffskontrollen'.
                'und Videodateien, bei und wird als eigenständiger Clip aufgeführt.',
        ],
    ],
];

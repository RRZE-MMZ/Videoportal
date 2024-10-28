<?php

return [
    'common' => [
        'episode' => 'Episode',
        'poster' => 'Poster',
        'title' => 'Titel',
        'access via' => 'Access via',
        'no clips' => 'Series has no clips',
        'clips without chapter(s)' => 'Clips without chapter(s)',
        'semester' => 'Semester',
        'duration' => 'Duration',
        'actions' => 'Actions',
        'edit series' => 'Edit series',
        'my series' => 'My series',
        'created at' => 'created at',
    ],
    'frontend' => [
        'show' => [
            'views' => ':counter views',
            'spanning in more semesters with the last one being' => 'Spanning in more semesters with the last one  '
                                                                    .'being :semester_title',
        ],
        'index' => [
            'Series index' => 'Series index',
            'no series' => 'Portal has no series yet',
        ],
    ],
    'backend' => [
        'actions' => [
            'create series' => 'Create new series',
            'select semester' => 'Please select semester',
            'select series' => 'Select series',
            'reorder series clips' => 'Reorder series clips',
            'add new clip' => 'Add new clip',
            'go to public page' => 'Go to public page',
            'edit metadata of multiple clips' => 'Edit metadata of multiple clips',
            'manage chapters' => 'Manage chapters',
            'mass update all clips' => 'Mass update all clips',
            'back to edit series' => 'Back to edit series',
        ],
        'Series administrator' => 'Series administrator',
        'Set a series owner' => 'Set a series owner',
        'Series has no owner yet' => 'Series has no owner yet',
        'Update Series' => 'Update series',
        'update series owner' => 'Update series owner',
        'Add a series member' => 'Add a series member',
        'actual episode' => 'Actual episode:',
        'no user series found' => 'You don\'t have any series yet. Please create one!',
        'mass update clip metadata for series' => 'Mass update clip metadata for series: 
            <span class="pl-2 font-semibold"> :seriesTitle </span>',
        'Series chapter has no clips' => 'Series chapter <span class="italic"> :chapterTitle </span> has no clips',
        'Select a series for clip' => 'Select a series for clip: :clip_title',
        'reorder clips for series' => 'Reorder clips for series: '.
                                            ' <span class="pl-2 italic"> :series_title </span>',
        'reorder series clips with chapters info' => 'Because your series has chapters, the clips order is adjusted '.
                                                       'on the series chapters management page.',
        'delete' => [
            'modal title' => 'Are you certain you wish to delete the series titled ":series_title"?',
            'modal body' => 'Please proceed with caution. Deleting this series will permanently remove all associated '.
                'clips and all their assets, including video files and transcriptions. Once deleted, the series will '.
                'no longer be accessible to users.',

        ],
    ],
];

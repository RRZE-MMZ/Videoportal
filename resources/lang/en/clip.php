<?php

return [
    'common' => [
        'no clips' => 'Keine Clips gefunden',
        'trending clips' => 'Trending Clips',
    ],
    'frontend' => [
        'index' => [
            'Clips index' => 'Clips index',
            'Portal has no clips yet!' => 'Portal has no clips yet',
        ],
        'show' => [
            'Back to clip edit page' => 'Back to clip edit page',
            'presenter video stream' => 'presenter video stream',
            'presentation video stream' => 'presentation video stream',
            'composite video stream' => 'composite video stream',
            'to LMS course' => 'LMS-Course Page',
            'views' => ':numVideo views',
        ],
        'not authorized to view video' => 'You are not authorized to view this video!',
        'this clip is exclusively accessible to logged-in users' => 'This clip is exclusively accessible to logged-in'.
                                                                    ' users.',
        'access to this clip is restricted to LMS course participants' => 'Access to this clip is restricted to LMS '.
                                                                            'course participants.',
        'this clip requires a password for access' => 'This clip requires a password for access. '.'
                                                        Please contact the Video Series administrators.',
        'comments' => 'Kommentare',
    ],
    'backend' => [
        'belongs to' => 'Belongs to: :series_title',
        'assign a series to this clip' => 'Assign a series to this clip',
        'upload a video' => 'Upload a video',
        'opencast video upload description' => 'will start a new video workflow. Thank you for your patience',
        'video files in dropzone' => 'Video files in dropzone',
        'please select one or more audio/video files' => 'Please select on or more audio/video files',
        'LMS Test Link' => 'LMS Test Link',
        'my clips' => 'My Clips',
        'with video files' => 'With video files',
        'actions' => [
            'go to clip public page' => 'Go to clip public page',
            'got to clip statistics page' => 'Got clip statistics page',
            'generate preview from frame' => 'Generate preview from frame',
            'upload an image' => 'Upload an image',
            're-trigger streaming smil files' => 'Re-trigger streaming smil files',
            'transfer files from drop zone' => 'Transfer files from dropzone',
            'upload already transcoded recording' => 'Upload an already transcoded recording',
            'remove series' => 'Remove series',
            'view available series' => 'View available series',
            'upload video' => 'Upload video',
            'add selected audio/video files to clip' => 'Add selected audio/video files to clip',
        ],
        'delete' => [
            'modal title' => 'Are you certain you wish to delete the clip titled ":clip_title"?',
            'modal body' => 'Please proceed with caution. Deleting this clip will permanently remove all associated '.
                            'assets, including video files and transcriptions. Once deleted, the clip will no '.
                            'longer be accessible to users.',
        ],
        'delete series connection' => [
            'modal title' => 'Would you like to disassociate the clip from the series titled ":series_title"?',
            'modal body' => 'By removing the clip from the series, it will no longer be a part of this series.'.
                'However, the clip will retain all its information, including metadata, access controls, and '.
                'video files, and will be listed as an independent, standalone clip.',
        ],
    ],
];

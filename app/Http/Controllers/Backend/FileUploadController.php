<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class FileUploadController extends Controller
{
    public function process(Request $request): string
    {
        // disable debugbar otherwise all debugbar output from it will be passed as a form input
        if (app()->environment('local')) {
            app('debugbar')->disable();
        }

        // We don't know the name of the file input, so we need to grab
        // all the files from the request and grab the first file.
        /** @var UploadedFile[] $files */
        $files = $request->allFiles();

        if (empty($files)) {
            abort(422, 'No files were uploaded.');
        }

        if (count($files) > 1) {
            abort(422, 'Only 1 file can be uploaded at a time.');
        }

        // Now that we know there's only one key, we can grab it to get
        // the file from the request.
        $requestKey = array_key_first($files);

        // If we are allowing multiple files to be uploaded, the field in the
        // request will be an array with a single file rather than just a
        // single file (e.g. - `csv[]` rather than `csv`). So we need to
        // grab the first file from the array. Otherwise, we can assume
        // the uploaded file is for a single file input and we can
        // grab it directly from the request.
        $file = is_array($request->input($requestKey))
            ? $request->file($requestKey)[0]
            : $request->file($requestKey);

        // Store the file in a temporary location and return the location
        // for FilePond to use.
        $originalName = $file->getClientOriginalName();

        return $file->storeAs(
            path: 'tmp/'.now()->timestamp.'-'.Str::random(20),
            name: $originalName,
        );
    }

    public function revert(Request $request)
    {
        // Retrieve the file path from the request
        $filePath = $request->input('filePath');

        // Delete the file from storage
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'file not found'], 404);
    }
}

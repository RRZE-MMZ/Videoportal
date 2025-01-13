<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Rules\ValidFile;
use Illuminate\Http\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
    ): RedirectResponse {
        // get the imageID from filepond.js request
        $imageID = (int) array_keys($request->query())[0];
        $image = Image::findOrFail($imageID);

        $validated = $request->validate([
            'image' => ['required', 'string', new ValidFile(['image/png', 'image/jpeg'])],
        ]);

        Storage::disk('images')->delete($image->file_name);

        $uploadedImage = new File(Storage::path($validated['image']));
        $newImage = pathinfo($image->file_name, PATHINFO_FILENAME).'.'.$uploadedImage->extension();
        // Copy the file from a temporary location to a permanent location and keep the old file name.
        $fileLocation = Storage::disk('local')->putFileAs(
            path: 'images',
            file: $uploadedImage,
            name: $newImage
        );

        $image->file_name = $newImage;
        $image->file_size = Storage::disk('local')->size('images/'.$newImage);
        $image->mime_type = Storage::disk('local')->mimeType('images/'.$newImage);
        $image->save();

        session()->flash('flashMessage', 'Image replaced successfully');

        return to_route('images.edit', $image);
    }
}

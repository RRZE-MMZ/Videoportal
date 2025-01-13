<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Traits\HandlesFilePondFiles;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Rules\ValidFile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mhor\MediaInfo\Exception\UnknownTrackTypeException;
use Mhor\MediaInfo\MediaInfo;

class ImagesController extends Controller
{
    use HandlesFilePondFiles;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('backend.images.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only portal admins can persist new images to DB
        $this->authorize('administrate-admin-portal-pages');
        $validated = $request->validate([
            'description' => ['required', 'string'],
            'image' => ['required', 'string', new ValidFile(['image/png', 'image/jpeg'])],
        ]);

        $image = $this->uploadAndCreateImage(filePath: $validated['image'], description: $validated['description']);

        return to_route('images.edit', $image);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        // Only portal admins can create new images.
        $this->authorize('administrate-admin-portal-pages');

        return view('backend.images.create');
    }

    /**
     * Display the specified resource.
     *
     * @throws UnknownTrackTypeException
     */
    public function show(Image $image, MediaInfo $mediaInfo)
    {
        $mediaInfoContainer = $mediaInfo->getInfo(Storage::disk('images')->path($image->file_name));

        return view('backend.images.show', compact('image', 'mediaInfoContainer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Image $image, MediaInfo $mediaInfo)
    {
        // Only portal admins can create new images.
        $this->authorize('administrate-admin-portal-pages');
        $mediaInfoContainer = $mediaInfo->getInfo(Storage::disk('images')->path($image->file_name));

        return view('backend.images.edit', compact('image', 'mediaInfoContainer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Image $image, MediaInfo $mediaInfo)
    {
        // Only portal admins can create new images.
        $this->authorize('administrate-admin-portal-pages');

        $validated = $request->validate([
            'description' => ['required', 'string'],
        ]);

        $image->description = $validated['description'];
        $image->save();
        $mediaInfoContainer = $mediaInfo->getInfo(Storage::disk('images')->path($image->file_name));

        session()->flash('flashMessage', 'Image updated successfully');

        return to_route('images.edit', $image)->with([$mediaInfoContainer]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        // Only portal admins can delete images.
        $this->authorize('administrate-admin-portal-pages');

        $image->delete();

        return to_route('images.index');
    }
}

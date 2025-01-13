<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Rules\ValidFile;
use Illuminate\Http\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChannelsUploadBannerImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Channel $channel, Request $request): RedirectResponse
    {
        $this->authorize('edit-channel', $channel);

        $validated = $request->validate([
            'image' => ['required', 'string', new ValidFile(['image/png', 'image/jpeg'])],
        ]);
        $oldBannerUrl = $channel->banner_url;
        $uploadedImage = new File(Storage::path($validated['image']));
        // Copy the file from a temporary location to a permanent location.
        $path = Storage::putFile(
            path: 'images/channels_banners',
            file: $uploadedImage,
        );

        $channel->banner_url = $path; // path will be images/channels_banners/imageID.png
        $channel->save();

        // delete the old banner if one is already set
        if (! is_null($oldBannerUrl)) {
            if (Storage::disk('local')->exists($oldBannerUrl)) {
                Storage::disk('local')->delete($oldBannerUrl);
            }
        }

        return to_route('channels.edit', $channel);
    }
}

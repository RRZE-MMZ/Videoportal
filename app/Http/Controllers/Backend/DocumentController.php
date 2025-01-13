<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Document;
use App\Models\Series;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'type' => ['required', Rule::in(['series', 'clip'])], // types are not dynamic
            'document' => ['file', 'mimes:pdf,xsl,doc'],
        ]);

        $model = match ($validated['type']) {
            'series' => 'App\Models\Series',
            'clip' => 'App\Models\Clip',
        };
        $resource = $model::findOrFail($validated['id']);

        $this->authorize('edit-'.str($validated['type'])->plural(), $resource);

        $document = Document::create([
            'name' => $validated['document']->getClientOriginalName(),
            'save_path' => $validated['document']
                ->store("{$validated['type']}_{$resource->id}", 'documents'),
        ]);

        $resource->addDocument($document);

        return back()->with('flashMessage', 'File uploaded successfully');
    }

    /**
     * View a series document on browser
     *
     *
     * @throws AuthorizationException
     */
    public function viewSeriesDocument(Series $series, Document $document): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('edit-series', $series);

        // file maybe not found especially in testing
        try {
            return response()->file(public_path('documents/').$document->save_path);
        } catch (Exception $exception) {
            Log::error($exception);

            return to_route('series.edit', $series);
        }
    }

    /**
     * View a clip document in browser
     *
     *
     * @throws AuthorizationException
     */
    public function viewClipDocument(Clip $clip, Document $document): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('edit-clips', $clip);

        // file maybe not found especially in testing
        try {
            return response()->file(public_path('documents/').$document->save_path);
        } catch (Exception $exception) {
            Log::error($exception);

            return to_route('clips.edit', $clip);
        }
    }

    /**
     * Delete a given document
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Document $document): RedirectResponse
    {
        if ($document->series->isNotEmpty()) {
            $this->authorize('delete-series', $document->series()->first());
        } else {
            $this->authorize('edit-clips', $document->clips()->first());
        }

        $document->delete();

        return back()->with('flashMessage', 'File deleted successfully');
    }
}

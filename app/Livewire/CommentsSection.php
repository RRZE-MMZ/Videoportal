<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\NewComment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class CommentsSection extends Component
{
    use AuthorizesRequests;

    public $model;

    public $content;

    public $messageText = '';

    public $messageType = '';

    public $type;

    protected $comments;

    protected array $rules = [
        'content' => 'required|min:3',
        'model' => 'required',
        'type' => 'required|string',
    ];

    public function mount($model): void
    {
        $this->model = $model;
    }

    /**
     * Post a comment for a clip
     *
     * @throws AuthorizationException
     */
    public function postComment(): void
    {
        $this->authorize('create-comment');

        $this->validate();

        $comment = $this->model->comments()->save(Comment::create([
            'content' => $this->content,
            'owner_id' => auth()->user()->id,
            'type' => $this->type,
        ]));

        $this->content = '';

        $this->model->refresh();

        // don't notify user for self posted comments
        if (auth()->user()->isNot($this->model->owner)) {
            if (is_null($this->model->owner)) {
                Notification::sendNow(User::admins()->get(), new NewComment($comment));
            } else {
                Notification::sendNow($this->model->owner, new NewComment($comment));
            }
        }

        $this->messageText = 'Comment posted successfully';
        $this->messageType = 'success';
        $this->fetchComments();
        $this->dispatch('updated');
    }

    /**
     * Get all comments for a clip
     */
    public function fetchComments(): void
    {
        $this->comments = $this->model->comments()->where('type', $this->type)->get();
    }

    /**
     * Delete a single comment
     *
     *
     * @throws AuthorizationException
     */
    public function deleteComment(Comment $comment): void
    {
        $this->authorize('delete-comment', $comment);

        $comment->delete();

        $this->model->refresh();
        $this->fetchComments();

        $this->messageText = 'Comment deleted successfully';
        $this->messageType = 'error';
        $this->dispatch('updated');
    }

    /**
     * Render Livewire component
     */
    public function render(): View
    {
        $this->comments = $this->model->comments()->where('type', $this->type)->get();

        return view('livewire.comments-section', ['comments' => $this->comments]);
    }
}

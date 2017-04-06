## List of events
 * Jeylabs\Vaultbox\Events\ImageIsUploading
 * Jeylabs\Vaultbox\Events\ImageWasUploaded
 * Jeylabs\Vaultbox\Events\ImageIsRenaming
 * Jeylabs\Vaultbox\Events\ImageWasRenamed
 * Jeylabs\Vaultbox\Events\ImageIsDeleting
 * Jeylabs\Vaultbox\Events\ImageWasDeleted
 * Jeylabs\Vaultbox\Events\FolderIsRenaming
 * Jeylabs\Vaultbox\Events\FolderWasRenamed
 * Jeylabs\Vaultbox\Events\ImageIsResizing
 * Jeylabs\Vaultbox\Events\ImageWasResized
 * Jeylabs\Vaultbox\Events\ImageIsCropping
 * Jeylabs\Vaultbox\Events\ImageWasCropped


## How to use
 * To use events you can add a listener to listen to the events.

    Snippet for `EventServiceProvider`

    ```php
    protected $listen = [
        ImageWasUploaded::class => [
            UploadListener::class,
        ],
    ];
    ```

    The `UploadListener` will look like:

    ```php
    class UploadListener
    {
        public function handle($event)
        {
            $method = 'on'.class_basename($event);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $event);
            }
        }

        public function onImageWasUploaded(ImageWasUploaded $event)
        {
            $path = $event->path();
            //your code, for example resizing and cropping
        }
    }
    ```

 * Or by using Event Subscribers

    Snippet for `EventServiceProvider`

    ```php
    protected $subscribe = [
        UploadListener::class
    ];
    ```

    The `UploadListener` will look like:

    ```php
    public function subscribe($events)
    {
        $events->listen('*', UploadListener::class);
    }

    public function handle($event)
    {
        $method = 'on'.class_basename($event);
        if (method_exists($this, $method)) {
            call_user_func([$this, $method], $event);
        }
    }

    public function onImageWasUploaded(ImageWasUploaded $event)
    {
        $path = $event->path();
        // your code, for example resizing and cropping
    }

    public function onImageWasRenamed(ImageWasRenamed $event)
    {
        // image was renamed
    }

    public function onImageWasDeleted(ImageWasDeleted $event)
    {
        // image was deleted
    }

    public function onFolderWasRenamed(FolderWasRenamed $event)
    {
        // folder was renamed
    }
    ```

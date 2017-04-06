## List of events
 * Jeylabs\Laravelfilemanager\Events\ImageIsUploading
 * Jeylabs\Laravelfilemanager\Events\ImageWasUploaded
 * Jeylabs\Laravelfilemanager\Events\ImageIsRenaming
 * Jeylabs\Laravelfilemanager\Events\ImageWasRenamed
 * Jeylabs\Laravelfilemanager\Events\ImageIsDeleting
 * Jeylabs\Laravelfilemanager\Events\ImageWasDeleted
 * Jeylabs\Laravelfilemanager\Events\FolderIsRenaming
 * Jeylabs\Laravelfilemanager\Events\FolderWasRenamed
 * Jeylabs\Laravelfilemanager\Events\ImageIsResizing
 * Jeylabs\Laravelfilemanager\Events\ImageWasResized
 * Jeylabs\Laravelfilemanager\Events\ImageIsCropping
 * Jeylabs\Laravelfilemanager\Events\ImageWasCropped


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

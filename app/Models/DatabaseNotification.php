<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification
{
    /**
     * Force notifications onto the application's default connection.
     *
     * The notifiable (User) lives on the shared `auth` connection, and a
     * plain morphMany would make notifications inherit that connection —
     * polluting the shared auth DB across HSE apps. Pinning to the default
     * connection keeps in-app notifications in this app's own database.
     */
    public function getConnectionName()
    {
        return config('database.default');
    }
}

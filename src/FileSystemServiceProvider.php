<?php

namespace Furrutiac\FileSystemManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class FileSystemServiceProvider extends ServiceProvider
{
  // ...existing code...

  public function boot()
  {
    $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    $this->publishes([
      __DIR__ . '/../config/file-system-manager.php' => config_path('file-system-manager.php'),
      __DIR__ . '/../resources/js' => resource_path('js'),
    ], 'furrutiac-file-manager');
  }
}

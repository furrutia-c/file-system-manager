<?php

namespace Furrutiac\FileSystemManager\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\StorageAttributes;

class FileSystemController
{
  public function index($path = '/')
  {
    $path = urldecode($path);
    try {
      $disk = Storage::disk('ftp');

      // Verificar conexión básica
      if (!$disk->exists('.')) {
        logger()->error('FTP - No se puede acceder al directorio raíz');
        abort(500, 'Error de conexión FTP');
      }

      // Obtener contenido con múltiples métodos
      $contents = [
        'via_listContents' => $disk->listContents($path, false)->toArray(),
        'via_directories' => $disk->directories($path),
        'via_files' => $disk->files($path)
      ];

      logger()->debug('Contenido FTP:', $contents);

      return Inertia::render('FileManager', [
        'contents' => $contents['via_listContents'],
        'currentPath' => $path
      ]);
    } catch (\Exception $e) {
      logger()->error('Error FTP completo:', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      abort(500, "Error interno del servidor");
    }
  }

  public function upload(Request $request)
  {
    $request->validate(['files.*' => 'required|file']);
    $path = urldecode($request->path ? $request->path . '/' : '');

    try {
      foreach ($request->file('files') as $file) {
        Storage::disk('ftp')->put($path . $file->getClientOriginalName(), file_get_contents($file));
      }
    } catch (\Exception $e) {
      return back()->withErrors(['ftp_error' => $e->getMessage()]);
    }

    return back();
  }

  public function move(Request $request)
  {
    $request->validate([
      'source' => 'required|string',
      'destination' => 'required|string'
    ]);

    $source = urldecode($request->source);
    $destination = urldecode($request->destination);

    try {
      Storage::disk('ftp')->move($source, $destination);
    } catch (\Exception $e) {
      return back()->withErrors(['ftp_error' => $e->getMessage()]);
    }

    return back();
  }

  public function delete(Request $request)
  {
    $path = urldecode($request->path);
    try {
      Storage::disk('ftp')->delete($path);
    } catch (\Exception $e) {
      return back()->withErrors(['ftp_error' => $e->getMessage()]);
    }
    return back();
  }

  public function createFolder(Request $request)
  {
    $request->validate(['name' => 'required|string']);
    $path = urldecode($request->path ? $request->path . '/' : '');
    $folderPath = $path . $request->name;

    try {
      Storage::disk('ftp')->makeDirectory($folderPath);
    } catch (\Exception $e) {
      return back()->withErrors(['ftp_error' => $e->getMessage()]);
    }

    return back();
  }
}

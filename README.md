# File System Manager
This package for Laravel allows you to connect to an FTP server and efficiently manage files and folders. It provides an intuitive interface for performing common operations such as uploading, downloading, deleting, moving, and renaming files, as well as creating and organizing directories directly from your web application.

## Key Features

- **FTP Connection**: Simplifies connecting to FTP servers for file and folder management.
- **File Management**: Full support for uploading, downloading, renaming, moving, and deleting files.
- **Folder Management**: Easily create, delete, and organize directories.
- **Modern Interface**: Uses **Tailwind CSS** for a clean and responsive design.
- **Reactive Frontend**: Built with **Inertia.js** and **Vue 3**, providing a seamless and dynamic user experience.
- **Jetstream Integration**: Requires the Laravel Jetstream stack with Inertia.js and Vue 3 for a fully integrated setup.

## Requirements

To use this package, ensure your Laravel application has the following stack configured:

1. **Laravel Jetstream**: For authentication and session management.
2. **Inertia.js**: To handle pages and navigation without full-page reloads.
3. **Vue 3**: For building reactive components on the frontend.
4. **Tailwind CSS**: For consistent and modern styling in the interface.

## Installation Guide

### Installing in Development Mode (Inside the `packages` Directory)

1. **Create the `packages` Directory**  
   If your Laravel project doesn’t already have a `packages` directory, create it at the root level of your project:
   ```bash
   mkdir packages
   ```
2. **Clone the Repository**
   ```bash
   git clone https://github.com/furrutia-c/file-system-manager.git packages/furrutiac/file-system-manager
3. **Update the `composer.json` File**
   ```
   "repositories": [
        {
            "type": "path",
            "url": "packages/furrutiac/file-system-manager",
            "options": {
                "symlink": true
            }
        }
    ],
4. **Install package**
   ````
   composer require furrutiac/file-system-manager:@dev

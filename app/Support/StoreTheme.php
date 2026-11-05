<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class StoreTheme
{
    public const DEFAULT = 'smart-minima-commerce';

    public static function root(string $slug): ?string
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) return null;
        return match ($slug) {
            self::DEFAULT => resource_path('themes/ayra'),
            'bold-contemporary' => resource_path('themes/bold-contemporary'),
            default => is_dir(storage_path('app/themes/'.$slug)) ? storage_path('app/themes/'.$slug) : null,
        };
    }

    public static function manifest(string $slug): ?array
    {
        $root = self::root($slug);
        if (! $root || ! is_file($root.'/theme.info.json') || ! is_file($root.'/theme.css')) return null;
        $data = json_decode((string) file_get_contents($root.'/theme.info.json'), true);
        if (! is_array($data) || ($data['slug'] ?? null) !== $slug) return null;
        foreach (['name', 'author', 'description'] as $key) {
            if (! is_string($data[$key] ?? null) || trim($data[$key]) === '' || strlen($data[$key]) > ($key === 'description' ? 500 : 80)) return null;
        }
        $thumbnail = $data['thumbnail'] ?? null;
        if ($thumbnail !== null && (! is_string($thumbnail) || ! preg_match('/^thumbnail\.(png|jpe?g|webp)$/', $thumbnail) || ! is_file($root.'/'.$thumbnail))) return null;
        return $data;
    }

    public static function all(): array
    {
        $slugs = [self::DEFAULT, 'bold-contemporary'];
        if (is_dir(storage_path('app/themes'))) foreach (File::directories(storage_path('app/themes')) as $path) $slugs[] = basename($path);
        $themes = [];
        foreach (array_unique($slugs) as $slug) if ($theme = self::manifest($slug)) $themes[$slug] = $theme;
        return $themes;
    }

    public static function activeSlug(): string
    {
        $slug = SiteSetting::getValue('active_theme', self::DEFAULT);
        return self::manifest($slug) ? $slug : self::DEFAULT;
    }

    public static function asset(string $slug, string $name): ?string
    {
        if (! self::manifest($slug) || ! preg_match('~^(?:theme\.css|thumbnail\.(?:png|jpe?g|webp)|assets/[a-z0-9-]+\.(?:png|jpe?g|webp|woff2))$~', $name)) return null;
        $path = self::root($slug).'/'.$name;
        return is_file($path) ? $path : null;
    }

    public static function install(UploadedFile $upload): string
    {
        if (! class_exists(ZipArchive::class)) throw ValidationException::withMessages(['theme_zip' => 'PHP ZipArchive is required to install themes.']);
        $zip = new ZipArchive();
        if ($zip->open($upload->getRealPath()) !== true) throw ValidationException::withMessages(['theme_zip' => 'The ZIP could not be opened.']);
        try {
            if ($zip->numFiles < 2 || $zip->numFiles > 50) throw ValidationException::withMessages(['theme_zip' => 'A theme ZIP must contain 2–50 files.']);
            $entries = []; $total = 0; $prefix = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];
                if (str_ends_with($name, '/')) continue;
                $parts = explode('/', $name);
                if (str_contains($name, '\\') || str_contains($name, '..') || str_starts_with($name, '/') || count($parts) > 3) throw ValidationException::withMessages(['theme_zip' => 'Unsafe file path in ZIP.']);
                $folder = count($parts) > 1 && $parts[0] !== 'assets' ? $parts[0] : '';
                if ($folder !== '' && ! preg_match('/^[a-z0-9-]+$/i', $folder)) throw ValidationException::withMessages(['theme_zip' => 'Unsafe theme folder name.']);
                if ($prefix === null) $prefix = $folder;
                if ($folder !== $prefix) throw ValidationException::withMessages(['theme_zip' => 'Keep all theme files in the same folder.']);
                $base = $folder === '' ? $name : substr($name, strlen($folder) + 1);
                if (! in_array($base, ['theme.info.json', 'theme.css', 'thumbnail.png', 'thumbnail.jpg', 'thumbnail.jpeg', 'thumbnail.webp'], true) && ! preg_match('~^assets/[a-z0-9-]+\.(?:png|jpe?g|webp|woff2)$~', $base)) throw ValidationException::withMessages(['theme_zip' => 'Only the manifest, CSS, thumbnail and assets images/fonts are allowed.']);
                if (isset($entries[$base])) throw ValidationException::withMessages(['theme_zip' => 'Duplicate file in theme ZIP.']);
                $total += (int) $stat['size'];
                if ($total > 10 * 1024 * 1024 || $stat['size'] > 8 * 1024 * 1024) throw ValidationException::withMessages(['theme_zip' => 'Theme ZIP is too large.']);
                $entries[$base] = $name;
            }
            if (! isset($entries['theme.info.json'], $entries['theme.css'])) throw ValidationException::withMessages(['theme_zip' => 'theme.info.json and theme.css are required.']);
            if (count(array_filter(array_keys($entries), fn (string $name) => str_starts_with($name, 'thumbnail.'))) > 1) throw ValidationException::withMessages(['theme_zip' => 'Include only one thumbnail.']);
            $manifest = json_decode((string) $zip->getFromName($entries['theme.info.json']), true);
            $slug = $manifest['slug'] ?? null;
            if (! is_string($slug) || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || in_array($slug, [self::DEFAULT, 'bold-contemporary'], true) || self::root($slug)) throw ValidationException::withMessages(['theme_zip' => 'Choose a unique lowercase theme slug.']);
            foreach (['name', 'author', 'description'] as $key) {
                if (! is_string($manifest[$key] ?? null) || trim($manifest[$key]) === '' || strlen($manifest[$key]) > ($key === 'description' ? 500 : 80)) throw ValidationException::withMessages(['theme_zip' => 'The manifest needs a name, author, and description.']);
            }
            if (isset($manifest['thumbnail']) && (! is_string($manifest['thumbnail']) || ! isset($entries[$manifest['thumbnail']]) || ! preg_match('/^thumbnail\.(png|jpe?g|webp)$/', $manifest['thumbnail']))) throw ValidationException::withMessages(['theme_zip' => 'The declared thumbnail is missing.']);
            foreach (array_keys($entries) as $base) {
                if (! str_starts_with($base, 'thumbnail.') && ! preg_match('/\.(png|jpe?g|webp)$/', $base)) continue;
                $image = $zip->getFromName($entries[$base]);
                if (! is_string($image) || ! in_array((getimagesizefromstring($image)['mime'] ?? null), ['image/png', 'image/jpeg', 'image/webp'], true)) throw ValidationException::withMessages(['theme_zip' => 'Theme images must be valid PNG, JPEG, or WebP files.']);
            }
            $css = $zip->getFromName($entries['theme.css']);
            if (! is_string($css) || strlen($css) > 1024 * 1024) throw ValidationException::withMessages(['theme_zip' => 'theme.css must be at most 1 MB.']);
            $path = storage_path('app/themes/'.$slug);
            File::ensureDirectoryExists($path);
            try {
                foreach ($entries as $base => $entry) {
                    $contents = $zip->getFromName($entry);
                    if (str_ends_with($base, '.woff2') && (! is_string($contents) || ! str_starts_with($contents, 'wOF2'))) throw ValidationException::withMessages(['theme_zip' => 'Invalid WOFF2 font.']);
                    File::ensureDirectoryExists(dirname($path.'/'.$base));
                    if (! is_string($contents) || file_put_contents($path.'/'.$base, $contents) === false) throw new \RuntimeException('Could not save theme file.');
                }
                if (! self::manifest($slug)) throw ValidationException::withMessages(['theme_zip' => 'Theme manifest or thumbnail is invalid.']);
            } catch (\Throwable $error) {
                File::deleteDirectory($path);
                throw $error;
            }
            return $slug;
        } finally {
            $zip->close();
        }
    }
}

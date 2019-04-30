<?php

namespace Yapo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

use Yapo\Folder;
use Yapo\Scene;

class CleanupController extends Controller
{
    protected function detachScenesFromFolders($collection)
    {
        foreach ($collection as $trash) {
            $scenes = $trash->scenes()->withTrashed()->get();

            foreach ($scenes as $scene) {
                // Check for matching scene
                $duplicate = Scene::where('name', $scene->name)->orWhere(function ($query) use ($scene) {
                    $query->where('codec_name', $scene->codec_name)->where('duration', $scene->duration)->where('size', $scene->size);
                })->with(['actors', 'tags', 'websites'])->first();

                if ($duplicate) {
                    if ($scene->actors->count()) {
                        $syncActors = $scene->actors->map->id->all();
                        $syncActorsResult = $duplicate->actors()->syncWithoutDetaching($syncActors);
                    }

                    // Migrate all tags
                    if ($scene->tags->count()) {
                        $syncTags = $scene->tags->map->id->all();
                        $syncTagsResult = $duplicate->tags()->syncWithoutDetaching($syncTags);
                    }

                    if ($scene->websites->count()) {
                        $syncWebsites = $scene->websites->map->id->all();
                        $syncWebsitesResult = $duplicate->websites()->syncWithoutDetaching($syncWebsites);
                    }

                    // Migrate rating
                    if ($duplicate->rating === 0 && $scene->rating > 0) {
                        $duplicate->rating = $scene->rating;
                    }

                    // Migrate plays
                    if ($duplicate->play_count === 0 && $scene->play_count > 0) {
                        $duplicate->play_count = $scene->play_count;
                    }

                    if ($duplicate->isDirty()) {
                        $duplicate->save();
                    }
                }

                $scene->actors()->withTrashed()->detach();
                $scene->websites()->detach();
                $scene->tags()->detach();

                $trash->scenes()->detach($scene->id);
            }
        }
    }

    protected function forceDeleteFolders($collection)
    {
        foreach ($collection as $trash) {
            try {
                $trash->forceDelete();
            } catch (QueryException $e) {
                foreach (Folder::onlyTrashed()->where('parent_id', $trash->id)->get() as $subTrash) {
                    $subTrash->forceDelete();
                }
                $trash->forceDelete();
            }
        }
    }

    protected function forceDeleteScenes()
    {
        $collection = Scene::onlyTrashed()->get();
        foreach ($collection as $item) {
            // Detach related tags
            $item->actors()->detach();
            $item->websites()->detach();
            $item->tags()->detach();

            // Detach from related folder
            if ($item->folder) {
                $item->folder()->withTrashed()->detach();
            }

            // Delete forever
            $item->forceDelete();
        }
    }

    public function folders()
    {

        if (request()->has('destroy')) {
            $collection = Folder::onlyTrashed()->get();

            $this->detachScenesFromFolders($collection);
            $this->forceDeleteFolders($collection);
            $this->forceDeleteScenes();

            echo Folder::onlyTrashed()->count()." folders in Trash.<br>";
            echo Scene::onlyTrashed()->count()." scenes in Trash.";

            return;
        }

        $collection = Folder::all();
        echo "Started with {$collection->count()}<br>";
        foreach ($collection as $item) {
            if (! File::exists($item->name)) {
                $item->delete();
            }
        }

        $total = Folder::withTrashed()->count();
        $collectionAfter = Folder::count();

        return "Completed with {$collectionAfter} .. {$total} Total";
    }

    public function scenes()
    {
        if (request()->has('destroy')) {
            $this->forceDeleteScenes();

            return 'Done';
        }

        $collection = Scene::all();
        echo "Started with {$collection->count()}<br>";
        foreach ($collection as $item) {
            if (! File::exists($item->path_to_file)) {
                $item->delete();
            }
        }

        $total = Scene::withTrashed()->count();
        $collectionAfter = Scene::count();

        return "Completed with {$collectionAfter} .. {$total} Total";
    }
}

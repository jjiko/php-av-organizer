<?php

namespace Yapo\Http\Controllers;

use App\Http\Controllers\Controller;
use Yapo\Scene;

class SceneController extends Controller
{
    public function delete()
    {
        abort_if(! request()->has('id'), 401);
        if (! $scene = Scene::find(request('id'))) {
            return redirect(url()->previous())->with('message', request('id').' was already deleted.. check archive.');
        }
        $videoDeleted = true;
        $thumbnailDeleted = true;
        if (file_exists($scene->path_to_file)) {
            $videoDeleted = unlink($scene->path_to_file);
        }
        if ($scene->thumbnail && file_exists(env('YAPO_MEDIA_PATH')."/$scene->thumbnail")) {
            $thumbnailDeleted = unlink(env('YAPO_MEDIA_PATH')."/$scene->thumbnail");
        }

        $sceneDeleted = $scene->delete();

        // @todo check for empty folder and delete that too

        response()->json($videoDeleted && $thumbnailDeleted && $sceneDeleted);

        return redirect(url()->previous())->with('message', request('id').' was deleted.');
    }

    public function update($id)
    {
        $scene = Scene::findOrFail($id);
        abort_if(! request()->has('rating'), 401);
        abort_if(! is_numeric(request('rating')), 401);
        $rating = (float) request('rating');
        $scene->rating = $rating;
        $result = $scene->save();

        if (! request()->ajax()) {
            return redirect()->back();
        }

        return response()->json(['result' => $result]);
    }
}

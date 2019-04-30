<?php

namespace Yapo\Http\Controllers;

use Carbon\Carbon;
use Yapo\Actor;
use Yapo\Scene;
use Yapo\SceneTag;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class YapoController extends Controller
{
    protected function play($sceneId)
    {
        $pidx = session('pidx', 0);

        if (! is_numeric($pidx)) {
            $pidx = 0;
        }

        if ($pidx > 5) {
            $pidx = 0;
        }

        session()->put('pidx', $pidx + 1);

        $vlc = env('VLC_PATH');
        $scene = Scene::findOrFail($sceneId);

        if (! File::exists($scene->path_to_file)) {
            $scene->delete();

            abort(404, 'Scene is missing from the system & has been moved to trash.');
        }

        $scene->play_count = $scene->play_count + 1;

        $screen = [3440, 1440];
        $sm = [688, 387];
        $md = [960, 720];
        $lg = [1920, 1080];
        $width = $sm[0];
        $height = $sm[1];
        // 16/9
        $options = [
            '--width' => $sm[0],
            '--height' => $sm[1],
            '--video-x' => 0 * $pidx,
            '--video-y' => 0 * $pidx,
        ];
        $tmp = [];
        foreach ($options as $a => $v) {
            $tmp[] = "{$a}={$v}";
        }
        // --qt-minimal-view
        $args = join(" ", $tmp);
        $cmd = "start vlc \"{$scene->path_to_file}\" {$args}";
        $handle = popen($cmd, "r");
        echo "'$handle'; ".gettype($handle)."\n";
        $read = fread($handle, 2096);
        echo $read;
        pclose($handle);
    }

    public function actor($id)
    {
        $actor = Actor::find($id);
        abort_if(! $actor, 404);

        return $actor;
    }

    public function scene()
    {
        if (request()->has('action')) {
            switch (request('action')) {
                case "play":
                    return $this->play(request('sceneId'));
            }
        }

        abort_if(! request()->has('id'), 404);

        $scene = Scene::with(['actors', 'tags', 'websites'])->where('id', request('id'))->first();

        abort_if(! $scene, 404);

        return view('scene.show', ['scene' => $scene]);
    }

    public function trash()
    {
        $sceneData = Scene::where('rating', '>', 0)->where('rating', '<', 2)->get();

        return view('scene.index', ['scenes' => $sceneData, 'delete' => true]);
    }

    public function scenes()
    {
        if (request()->has('tag')) {
            $tagId = is_numeric(request('tag')) ? request('tag') : SceneTag::where('name', request('tag'))->first()->id;
            // Scenes with requested tag
            $scenes = Scene::whereHas('tags', function ($query) use ($tagId) {
                $query->where('scenetag_id', $tagId);
            })->get();
        }

        if (! isset($scenes)) {
            // All scenes
            $scenes = Scene::with(['actors', 'tags', 'websites'])->get();
        }

        $filtered = $scenes->filter(function ($scene) {
            if (strtolower(request('tag')) === 'vr') {
                $excludedTags = true;
            } else {
                $excludedTags = $scene->tags->filter(function ($tag) {
                        return in_array(strtolower($tag->name), ['vr']);
                    })->count() < 1;
            }

            $excludedActors = $scene->actors->filter(function ($tag) {
                    return in_array(strtolower($tag->name), ['littlesubgirl', 'aiko doll']);
                })->count() < 1;

            $lowRating = $scene->rating < 5;

            return $excludedTags || ($excludedActors && $lowRating);
        });

        if (request()->has('filter')) {
            $filter = ! is_array(request('filter')) ? [request('filter')] : request('filter');
            if (in_array("no-actor", $filter)) {
                $filtered = $filtered->filter(function ($scene) {
                    $noActors = $scene->actors->count() === 0;
                    $notCompilation = $scene->tags->filter(function ($tag) {
                            return in_array(strtolower($tag->name), ['compilation', 'collection', 'pornhub', 'asian', 'nyoshin', 'Asian.Female']);
                        })->count() < 1;
                    $notGenericVendor = $scene->websites->filter(function ($website) {
                            return in_array(strtolower($website->name), ['youporn', 'heyzo', 'wowgirls', 'pornhub', 'japanhdv']);
                        })->count() < 1;

                    return $noActors && $notCompilation && $notGenericVendor;
                });
            }
        }

        if (request()->has('sort')) {
            $sort = ! is_array(request('sort')) ? [request('sort')] : request('sort');
            if (in_array("recent", $sort)) {
                $sorted = $filtered->sortByDesc(function ($scene) {
                    return Carbon::parse($scene->date_added)->timestamp;
                });
            }
        }

        $sceneData = $sorted ?? $filtered;

        return view('scene.index', ['scenes' => $sceneData->take(40), 'scenes_total' => $sceneData->count()]);
    }

    public function stats()
    {
        return view('yapo.stats');
    }

    public function index()
    {
        $query = Scene::with(['actors', 'tags', 'websites'])->inRandomOrder();
        if (request()->has('rating')) {
            $query->where('rating', '>=', request('rating', '0'));
        }
        $scenes = $query->get();
        if (request()->has('filter')) {
            $filter = ! is_array(request('filter')) ? [request('filter')] : request('filter');
            if (in_array('no-rating', $filter)) {
                $filtered = $scenes->filter(function ($scene) {
                    $noExcludedSites = $scene->websites->filter(function ($tag) {
                            return in_array(strtolower($tag->name), ['youporn', 'chaturbate', 'manyvids', 'nofacegirl']);
                        })->count() < 1;

                    $noExcludedTags = $scene->tags->filter(function ($tag) {
                            return in_array(strtolower($tag->name), ['vr']);
                        })->count() < 1;
                    $noExcludedActors = $scene->actors->filter(function ($tag) {
                            return in_array(strtolower($tag->name), ['littlesubgirl', 'aiko doll']);
                        })->count() < 1;
                    $noRating = $scene->rating == 0;

                    return $noRating && $noExcludedTags && $noExcludedActors && $noExcludedSites;
                });
            }
        } else {
            $filtered = $scenes->filter(function ($scene) {
                $excludedTags = $scene->tags->filter(function ($tag) {
                        return in_array(strtolower($tag->name), ['vr']);
                    })->count() < 1;
                $excludedActors = $scene->actors->filter(function ($tag) {
                        return in_array(strtolower($tag->name), ['littlesubgirl']);
                    })->count() < 1;

                $lowRating = $scene->rating > request('rating', 5);

                return $excludedTags && ($excludedActors && $lowRating);
            });
        }

        return view('scene.index', [
            'scenes' => $filtered->take(request('limit', 16)),
            'scenes_total' => $filtered->count(),
        ]);
    }

    public function thumbnail($path)
    {
        return response(file_get_contents('c:/disk1/opt/yapo/YAPO/videos/media/'.$path), 200, ['Content-Type' => 'image/jpeg']);
    }
}

<?php

namespace Yapo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    public $table = 'folder';

    public function scenes()
    {
        return $this->belongsToMany(Scene::class, 'folder_scenes', 'folder_id', 'scene_id');
    }
}

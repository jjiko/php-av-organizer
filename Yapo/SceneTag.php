<?php

namespace Yapo;

use Illuminate\Database\Eloquent\Model;

class SceneTag extends Model
{
    public $table = 'scenetag';

    public function scenes()
    {
        return $this->belongsToMany(Scene::class);
    }
}

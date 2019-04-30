<?php

namespace Yapo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scene extends Model
{
    const CREATED_AT = 'date_added';

    const UPDATED_AT = 'modified_date';

    public $dateFormat = 'Y-m-d H:i:s.u';

    use SoftDeletes;

    public $table = 'scene';

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'scene_actors', 'scene_id', 'actor_id');
    }

    public function folder()
    {
        return $this->belongsToMany(Folder::class, 'folder_scenes');
    }

    public function tags()
    {
        return $this->belongsToMany(SceneTag::class, 'scene_scene_tags', 'scene_id', 'scenetag_id');
    }

    public function websites()
    {
        return $this->belongsToMany(Website::class, 'scene_websites', 'scene_id', 'website_id');
    }

    public function __toString()
    {
        return $this->name;
    }
}

<?php
namespace Yapo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actor extends Model {
    use SoftDeletes;

    public $table = 'actor';
}

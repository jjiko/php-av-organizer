<?php

namespace Yapo\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Str;
use Yapo\Support\Ffprobe as FfprobeObj;

class Ffprobe extends Command
{

    protected $signature = 'ffprobe {filename}';

    public function handle()
    {
        $filename = $this->argument('filename');
        $info = new FfprobeObj("E:\Video\Name\Elle Rose\Elle Rose - Backdoor Loving [21EroticaAnal].mp4");
        $vi = $info->streams[0];
        $title = Str::slug(basename($info->format->filename));
        dd(vsprintf("%s-%s.%s.%s", [$vi->height, $title, $vi->codec_name, $vi->duration_ts]));
        dd($info);
    }
}

<div class="scene">
    <div style="background-color: black">
        <a href="/scene?id={{ $scene->id }}" target="_blank">
            <small style="color: white">{{ $scene }}</small>
        </a> - <a href="//localhost:8000/#!/scene/{{ $scene->id }}" target="_blank">YAPO</a>
        @if(isset($delete) && $delete)
            <form action="/scene" method="post">
                <input type="hidden" name="_method" value="delete">
                <input type="hidden" name="id" value="{{ $scene->id }}">
                <button class="btn btn-default" type="submit">Delete</button>
            </form>
        @endif
    </div>
    <a href="/scene?action=play&sceneId={{ $scene->id }}" target="_blank" class="js-play" data-action="play">
        <img class="scene-img" data-src="{{ $scene->thumbnail }}" src="data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAUA
AAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO
    9TXL0Y4OHwAAAABJRU5ErkJggg==" alt="{{ $scene }}">
    </a>
    <div class="scene-rating">
        <form id="rating{{$scene->id}}" action="/scene/{{ $scene->id }}" method="POST" data-role="rating">
            <input type="hidden" name="_method" value="PUT">
            <div style="display: grid; grid-template-columns: repeat(22, 1fr)">
                <label style="text-align:center">x<br><input type="radio" name="rating" value="-1"></label>
                @for($i=0;$i <= 10;$i+=0.5)
                    <label style="text-align:center">{{ $i }}<br><input type="radio" name="rating" value="{{ $i }}"{{ $i == $scene->rating ? ' checked="checked"' : '' }}></label>
                @endfor
            </div>
        </form>
    </div>
    <div class="scene-tags">
        <div class="tags">
            @foreach($scene->actors as $actorTag)
                <a class="js-tag tag actor" href="/actor/{{$actorTag->id}}" target="_blank" data-actor-id="{{ $actorTag->id }}">
                    {{ $actorTag->name }}
                </a>
            @endforeach
            @foreach($scene->tags as $sceneTag)
                <a class="js-tag tag" href="/scenes?tag={{$sceneTag->id}}" target="_blank" data-scenetag-id="{{ $sceneTag->id }}">
                    <small>{{ $sceneTag->name }}</small>
                </a>
            @endforeach
            @foreach($scene->websites as $website)
                <a class="js-tag tag" href="//localhost:8000/#!/website/{{ $website->id }}" target="_blank">
                    <small>{{ $website->name }}</small>
                </a>
            @endforeach
        </div>
    </div>
</div>

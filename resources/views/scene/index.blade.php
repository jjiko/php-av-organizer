<!doctype html>
<style>
    html, body {
        font-family: arial, sans-serif;
        margin: 0;
    }

    .scenes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(calc(100% / 3), 1fr));
    }

    .scene-img {
        width: 100%;
        height: auto;
        min-height: 100px;
    }

    .tags {
        display: inline-block;
    }

    .tag {
        text-decoration: none;
    }
</style>
<header>
    <a class="btn" href="/">Random Scenes</a>
    <a class="btn" href="/?filter[]=no-rating">Not rated</a>
    <a class="btn" href="/?rating=7">7+</a>
    <a class="btn" href="/?rating=9">9+</a>
    <a class="btn" href="/?limit=100">100 Random Scenes</a>
    <a class="btn" href="/scenes">All Scenes</a>
    <a class="btn" href="/scenes?sort=recent">Recent</a>
    <a class="btn" href="/scenes?sort=recent&filter[]=no-actor">Recent (no actor)</a>
    <a class="btn" href="/scenes?sort=recent&tag=vr&filter[]=no-actor">VR (no actor)</a>
    <a class="btn" href="/stats">Stats</a>
    <h2>
        Matching {{ $scenes->count() }}
        @if($scenes_total)
            {{ "of {$scenes_total}" }}
        @endif
        scenes
    </h2>
</header>
<div class="scenes">
    @foreach($scenes as $scene)
        @include('scene.scene')
    @endforeach
</div>
<script type="module">
    import {ImageIntersectionObserver} from '/js/image.js';

    const ImageObserver = new ImageIntersectionObserver();
    const rating = document.querySelectorAll('[name=rating]');

    function matches(el, selector) {
        return (el.matches || el.matchesSelector || el.msMatchesSelector || el.mozMatchesSelector || el.webkitMatchesSelector || el.oMatchesSelector).call(el, selector);
    }

    function getParents(el) {
        const out = [];
        for (let p = el && el.parentElement; p; p = p.parentElement) {
            out.push(p);
        }
        return out;
    }

    function parents(el, selector) {
        const parentList = getParents(el);
        let result = null;
        parentList.forEach((parent) => {
            if (typeof selector !== 'undefined' && matches(parent, selector)) {
                result = parent;
            }
        });

        return result;
    }

    Array.from(document.querySelectorAll('a.js-play')).forEach((el) => {
        el.addEventListener('click', (evt) => {
            fetch(el.getAttribute('href')).then((response) => {
                console.log(response);
            });
            evt.preventDefault();
        });
    });

    Array.from(rating).forEach((el) => {
        el.addEventListener('change', (evt) => {
            const form = parents(evt.target, 'form');
            const selected = form.querySelector(':checked');
            const data = {
                rating: selected.value
            };
            fetch(form.action, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data),
            }).then(response => console.log(response.json()));
        });
    });
</script>

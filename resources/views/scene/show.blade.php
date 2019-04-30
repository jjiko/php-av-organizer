<!doctype html>
{{ $scene->rating }}
<form id="rating" action="/scene/{{ $scene->id }}" method="POST">
    <input type="hidden" name="_method" value="PUT">
    @for($i=0;$i <= 10;$i+=0.5)
        <label><input type="radio" name="rating" value="{{ $i }}"{{ $i == $scene->rating ? ' checked="checked"' : '' }}>{{ $i }}</label>
    @endfor
</form>
@foreach($scene->getAttributes() as $propName => $prop)
    <strong>{{ $propName }}</strong><br>
    {{ $prop }}<br><br>
@endforeach
<script>
    const rating = document.querySelectorAll('[name=rating]');
    Array.from(rating).forEach((el) => {
        el.addEventListener('change', (evt) => {
            document.querySelector('#rating').submit();
        });
    });
</script>

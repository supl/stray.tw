var Stray = { };
Stray.HTMLGenerator = { };

Stray.HTMLGenerator.Thumbnail = function (animal) {
    var template = `
    <div class="image_container">
        <a target="_blank" href=animals/{{ _animal_id }}>
            <img src={{ _url }}>
        </a>
    </div>`;

    return Mustache.render(template, {
        _url: animal.album_file,
        _animal_id: animal.animal_id,
    });
};

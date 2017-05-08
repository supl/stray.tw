var Stray = { };
Stray.HTMLGenerator = { };

Stray.HTMLGenerator.Thumbnail = function (animal) {
    var template = `
    <div class="image_container">
        <img src={{ _url }}>
    </div>`;

    return Mustache.render(template, {
        _url: animal.album_file,
    });
};

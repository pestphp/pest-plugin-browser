<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

final class ImageDiffSlider
{
    public static function generate(string $expectedImage, string $actualImage, string $title): string
    {
        $expected = base64_encode($expectedImage);
        $actual = base64_encode($actualImage);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>$title</title>
        </head>
        <body>
            <image-compare>
                <img slot="image-1" alt="Expected" src="data:image/png;base64,$expected"/>
                <img slot="image-2" alt="Actual" src="data:image/png;base64,$actual"/>
            </image-compare>
            <script src="https://unpkg.com/@cloudfour/image-compare/dist/index.min.js"></script>
        </body>
        </html>
        HTML;
    }
}

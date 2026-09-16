<?php

return [
    'uploads' => [
        'max_bytes' => 20 * 1024 * 1024,
        'allowed_mimes' => ['image/jpeg','image/png','image/webp','image/gif'],
        'allowed_ext' => ['jpg','jpeg','png','webp','gif'],
    ],
    'html_allowlist' => [
        'tags' => [
            'p','br','strong','em','u','s','span','blockquote','code','pre',
            'h1','h2','h3','h4','h5','h6','ul','ol','li','a','img','figure','figcaption',
            'oembed','iframe','table','thead','tbody','tfoot','tr','th','td',
            'video','audio','source'
        ],
        'attrs' => [
            'a' => ['href','title','rel','target'],
            'img' => ['src','alt','title','width','height','loading'],
            'ol' => ['type','start','reversed'],
            'ul' => ['type'],
            'oembed' => ['url'],
            'iframe' => ['src','width','height','frameborder','allow','allowfullscreen','title','scrolling'],
            'table' => ['border','cellpadding','cellspacing'],
            'td' => ['colspan','rowspan'],
            'th' => ['colspan','rowspan','scope'],
            'video' => ['src','controls','width','height','poster','autoplay','loop','muted'],
            'audio' => ['src','controls','autoplay','loop','muted'],
            'source' => ['src','type'],
            '*' => ['class', 'style', 'id', 'dir', 'lang', 'data-mce-style'],
        ],
        'protocols' => ['http','https','mailto','tel'],
    ],
    'csp' => [
        'dev' => [
            "default-src 'self'",
            "img-src 'self' data: blob:",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "connect-src 'self' ws: http: https:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com",
            "media-src 'self' blob: https:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ],
        'prod' => [
            "default-src 'self'",
            "img-src 'self' data: https:",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "script-src 'self' 'unsafe-inline'",
            "connect-src 'self' https:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com",
            "media-src 'self' https:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ],
    ],
];

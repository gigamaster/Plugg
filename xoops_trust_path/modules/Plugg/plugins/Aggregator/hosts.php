<?php
$host_matches = array(
    '/^([A-Z0-9-_]+)\.seesaa\.net$/i',
    '/d\.hatena\.ne\.jp/i',
    '/blogs\.yahoo\.co\.jp/i',
    '/blog\.livedoor\.jp/i',
    '/^([A-Z0-9-_]+)\.fc2\.com$/i',
    '/^([A-Z0-9-_]+)\.blogspot\.com$/i',
    '/plaza\.rakuten\.co\.jp/i'
);

$host_replacements = array(
    '$1.up.seesaa.net',
    'f.hatena.ne.jp',
    'img.blogs.yahoo.co.jp',
    'image.blog.livedoor.jp',
    'fc2.com',
    'bp.blogspot.com',
    'image.space.rakuten.co.jp'
);
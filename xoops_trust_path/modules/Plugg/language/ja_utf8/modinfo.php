<?php
$const_prefix = '_MI_' . strtoupper($module_dirname);

if (!defined($const_prefix)) {
    define($const_prefix, 1);

    define($const_prefix . '_NAME', 'コミュニティ');
    define($const_prefix . '_DESC', 'Plugg module for XOOPS powered by Sabai Framework');

    // Admin menu
    define($const_prefix . '_ADMENU_XROLES', 'ロール割り当て（グループ別）');

    define($const_prefix . '_C_MODRW', 'mod_rewriteを使用する');
    define($const_prefix . '_C_MODRWD', sprintf('「はい」を選択するとmod_rewrite用のURLを表示するようになります。なお、%1$s/.htaccessへと下記のような設定を追加してmod_rewriteによるURL変換が行われるようにする必要があります。<br /><br /><code>RewriteEngine on<br />RewriteCond %%{REQUEST_FILENAME} !-f<br />RewriteCond %%{REQUEST_FILENAME} !-d<br />RewriteRule ^(.+)$ modules/%2$s/index.php?q=/$1 [E=REQUEST_URI:/modules/%2$s/index.php?q=/$1,L,QSA]</core>', XOOPS_ROOT_PATH, $module_dirname));
    define($const_prefix . '_C_MODRWF', 'mod_rewriteの表示URL');
    define($const_prefix . '_C_MODRWFD', '表示されるURLのフォーマットを入力してください。%1$sはリクエストルート（例: /user/2）を表し、%2$sはリクエストパラメータ（例: foo=bar）、%3$sはリクエストパラメータの前に「?」を付加したもの（例: ?foo=bar）を表します。');
    define($const_prefix . '_C_DEBUG', 'デバグメッセージを表示する');
    define($const_prefix . '_C_DEBUGD', '「はい」を選択した場合、Pluggが出力するデバグメッセージを表示します。サイト公開時には常に「いいえ」を選択しておくことをお勧めします。');
    define($const_prefix . '_C_DPLUG', 'デフォルトプラグイン');
    define($const_prefix . '_C_DPLUGD', 'Pluggのトップページへとアクセスがあった場合に表示するプラグインです。プラグイン名を英数小文字で入力してください。');
    define($const_prefix . '_C_CRONK', 'Cronキー');
    define($const_prefix . '_C_CRONKD', sprintf('Cronを実行するのに必要な秘密キーです。Cron実行時にこのキーの値を渡してください。例: /usr/bin/php %s/cron.php --key=キーの値', XOOPS_ROOT_PATH));
}
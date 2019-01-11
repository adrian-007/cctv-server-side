<?php
$paths = glob('[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]', GLOB_ONLYDIR);
define('ROOT_DIR', preg_replace('/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/') . '/', '', __DIR__));
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>CCTV</title>

        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    </head>

    <body>
        <main role="main" class="container">

            <div class="starter-template">
                <h1>CCTV Video Listing</h1>

                <?php if (!empty($paths)) { ?>
                    <a href="<?= ROOT_DIR ?>/live/playlist.m3u8">Live stream</a>
                    <hr/>
                <?php } ?>

                <h2>Streams by date</h2>

                <?php foreach ($paths as $path) { ?>
                    <a href="<?= ROOT_DIR ?>/<?= $path ?>/playlist.m3u8"><?= $path ?></a>
                    <br/>
                <?php } ?>

            </div>
        </main>
    </body>
</html>

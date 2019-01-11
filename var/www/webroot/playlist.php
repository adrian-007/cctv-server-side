<?php
define('ROOT_DIR', preg_replace('/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/') . '/', '', __DIR__));

$isLive = isset($_GET['live']) && intval($_GET['live']) !== 0;
if (!isset($_GET['date']))
{
    if (!$isLive)
    {
        http_response_code(400);
    }

    $dirs = glob('[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]' , GLOB_ONLYDIR);
    if (empty($dirs))
    {
        http_response_code(404);
    }

    $lastDir = end($dirs);

    header("Location: " . ROOT_DIR . "/" . basename(__FILE__) . "?date=${lastDir}&live=1");
    die();
}
else if (preg_match('/\d{4}-\d{2}-\d{2}/', $_GET['date']) === FALSE || !is_dir($_GET['date']))
{
    http_response_code(404);
}

$targetDuration = isset($_GET["target-duration"]) ? doubleval($_GET["target-duration"]) : 10.0;

function GetVideoDuration($path)
{
    if (is_file($path . ".duration"))
    {
        return doubleval(file_get_contents($path . ".duration"));
    }

    global $targetDuration;
    return $targetDuration;
}

$dirs = glob('[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]' , GLOB_ONLYDIR);
$targetDir = $_GET['date'];

if (isset($_GET['live']) && intval($_GET['live']) !== 0)
{
    $paths = [];
    $startGrabbingFiles = false;
    $isListComplete = false;

    foreach ($dirs as $dir)
    {
        if ($dir === $targetDir)
        {
            $startGrabbingFiles = true;
        }

        if ($startGrabbingFiles)
        {
            foreach (glob("${dir}/video_*.ts") as $path)
            {
                $paths[] = $path;
            }
        }
    }
}
else
{
    $paths = glob("${targetDir}/video_*.ts");
    $isListComplete = (!empty($dirs) && end($dirs) !== $targetDir);
}

$sequence = 0;

if (!empty($paths))
{
    $top = array_values($paths)[0];
    if (preg_match('/\w+_(\d+)\.ts/', $top, $matches))
    {
        $sequence = intval($matches[1]);
    }
}

header("Content-Type: application/vnd.apple.mpegurl");
header("Content-Disposition: attachment;filename=playlist.m3u8");
header("Pragma: no-cache");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Expires: 0");

?>
#EXTM3U
#EXT-X-VERSION:6
#EXT-X-INDEPENDENT-SEGMENTS
#EXT-X-ALLOW-CACHE:YES
#EXT-X-PLAYLIST-TYPE:EVENT
#EXT-X-PLAYLIST-TYPE:<?= $isListComplete ? "VOD" : "EVENT"; ?>

#EXT-X-TARGETDURATION:<?= $targetDuration; ?>

#EXT-X-MEDIA-SEQUENCE:<?= $sequence; ?>


<?php foreach($paths as $path) { ?>
#EXTINF:<?= number_format(GetVideoDuration($path), 5, '.', ''); ?>,
<?= ROOT_DIR ?>/<?= $path; ?>

#EXT-X-DISCONTINUITY

<?php } ?>

<?php
    if ($isListComplete)
    {
        echo "#EXT-X-ENDLIST\n";
    }
?>

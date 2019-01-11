# CCTV - server side
This is a repository with support scripts for CCTV on Raspberry Pi project.

---

## Web Application
There are two PHP scripts - one that will help list available video files, group them by date and list in index page, and second that will help generate a m3u8 playlist for a given date.

### Requirements
Web server with support of fairly recent version of PHP.

### Installation
index.php and playlist.php must be placed in the root directory of remote location where video segment files are being uploaded. Script expects directories with video files to have a date as a name in YYYY-MM-DD format.

### Playlist usage
playlist.php will accept following arguments:
- ``date`` - specifies a date for which playlist will be generated. If corresponding directory with video files doesn't exist, 404 error is returned.
- ``live`` - if set to 1, it will continue generating playlist past specified date. If date is not specified, script will redirect client to itself with ``date`` parameter set to latest possible value.

Example calls:

- ``http://server/playlist.php?date=1970-01-01`` - generates playlist for 1970-01-01 and only that date, even if next is available.
- ``http://server/playlist.php?date=1970-01-01&live=1`` - generates playlist for 1970-01-01 and continues to next day, if available.
- ``http://server/playlist.php?live=1`` - looks up latest possible date and sends ``Location`` HTTP header with ``date`` parameter set to valid value.

---

## MPEGTS-concat service
Since one day of continuous video stream is almost 4 GB in size, server storage capacity limit can be quickly reached. On the other hand, re-encoding segment files with H.265 codec will yield around 380 MB of video - that is a significant save in storage space. In order to perform a conversion, ``mpegts-concat`` script and ``mpegts-concat.service`` files are provided so that conversion could be done directly on the server.

Conversion is performed on segments that are at least 2 days old. This delay is due to the fact that Camera Streamer manages a fixed backlog of files and during transition from one day to another it could send remaining files to previous day stream. Since we need segment file list to be complete before starting a conversion and we need it to be in perfect order, service will wait 48 hours for additional backlog elements. Since Streamer has an expiration date set on backlog, 48 hours + conversion time should suffice as a sane delay.

After conversion the resulting file called ``video.mp4`` along with checksum file ``video.mp4.sha512sum`` will replace segment files - they'll be permanently deleted.

Performing a conversion on 10-second segments for 24 hours of video files takes approx. 12 hours in OpenVZ container with 3 cores and 4 GB of RAM.

### Requirements
Script expects following packages / binaries:
- inotifywait for watching for new directories with video segment files
- FFmpeg compiled with libx265 support
- sha512sum

### Running
Working directory for service script must be set to root directory of remote location where video segment files are being uploaded. Script expects directories with video files to have a date as a name in YYYY-MM-DD format.

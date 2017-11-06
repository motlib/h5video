# h5video

h5video is a Mediawiki extension to embed video files in HTML 5 conforming video
tags. 

Currently the extension is limited to exactly one video source width
`video/mpeg4` format, usually indicated by the `.mp4` file extension.

Probably this will not change, as at this time most common and up-to-date
browsers support the MP4 + ACC format. So there seems to be not much need to
support alternative sources in WEBM or OGG format.


## License

This software is distributed under the terms of the MIT license. See
the `COPYING` file for details.


## Installation

Install the extension as usual for Mediawiki:

- Copy or `git clone` this project to a subfolder named `h5video` in the
  `extension` folder of your mediawiki installation.
  
- Add a line to your `LocalSettings.php`, e.g. at the end:

        wfLoadExtension('h5video');
    
- Go to the `Special:Version` page of your Mediawiki to verify that
  the `h5video` extension is loaded.

## Usage

Place a <video>...</video> tag in a wiki page. Write it in one of the following 
ways:

Embed a video file that has been uploaded to MediaWiki:

    <video width="640" height="360">File:MediaFile.mp4</video>
    
Embed a video file from another path on the same server (relative path) or on
another server (URL starting with `http` or `https`):

    <video width="640" height="360">../../LocalPath/MyFile.mp4</video>
    <video width="640" height="360">http://myhost/MyFile.mp4</video>

The `width` and `height` attributes are optional and default to 640 and 360
pixels. 


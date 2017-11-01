# h5v

Mediawiki extension to create HTML 5 conform video tags. 

## License

This software is distributed under the terms of the MIT license. See
the ``LICENSE.rst`` file for details.


## Installation

Install the extension as usual for mediawiki:

- Copy or `git clone` this project to a subfolder named ``h5v`` in the
  extension folder of your mediawiki setup.
  
- Add a line to your ``LocalSettings.php``, e.g. at the end::

    wfLoadExtension('h5v');
    
- Go to the `Special:Version` page of your Mediawiki to verify that
  the h5v extension is loaded.

## Usage

Use the video tag in any wiki page as follows:

    <video width="640" height="360">File:MediaFile.mp4</video>
    <video width="640" height="360">../../LocalPath/MyFile.mp4</video>
    <video width="640" height="360">http://myhost/MyFile.mp4</video>


The MediaFile is either a file uploaded to the wiki or a path / url
pointing to an external source.

The attributes for width and height are optional and default to 640 x
360.



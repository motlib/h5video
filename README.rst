mwext-mejs
==========

Mediawiki extension to use MediaElement.js media player.

License
-------

This software is distributed under the terms of the MIT license. See
the ``LICENSE.rst`` file for details.

This software includes parts (the ``build`` folder) of the
MediaElements media player by John Dyer. For details please refer to
the project website http://mediaelementjs.com/ and the source code on
Github at https://github.com/johndyer/mediaelement.


Installation
------------

Install the extension as usual for mediawiki:

- Copy this project to a subfolder named ``mejs`` in the extension
  folder of your mediawiki setup.
  
- Add a line to your ``LocalSettings.php``, e.g. at the end::

    wfLoadExtension('mejs');
    
- Go to the `Special:Version` page of your Mediawiki to verify that
  the mejs extension is loaded.

Usage
-----

Use the video tag in any wiki page as follows::

  <video width="640" height="480">http://myhost/myfile.mp4<video>



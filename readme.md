# Login

*A simple script to create resctricted areas in your projects*

_(Need php 5.5)_

## How to use out of the box:

Download login and login form in your script dir, create a global config users like this :  
**$GLOBALS['config']['users']['john'] = array('hash' => '$2y$10$bF10DtUBK5U0VMOna.QRZODLxjG9H23fMrwfSFwyieDg.MZg10Lnm',);**  
_Username john, password doe_

Use userWriter::returnHash() to generate your user:  
**echo userWriter::returnHash('doe');**  
And copy this informations in your config file.  
**BCRYPT is the default hash method**. 

Optional : Add sessionExpire time (in seconds) :  
**$GLOBALS['config']['sessionExpire'] = 3600;**

Call login.php  

Login !  

## How to use with http authentication and htpasswd compatibility (*nix only)

Add your users in htpasswd file with htpasswd cli :  
**htpasswd -cd myfile myuser**  
**htpasswd -d myfile myseconduser**  

Define this constants :  
**define('LOGIN_METHOD', 'http-basic');**  
**define('STORAGE_METHOD', 'apacheFileBasic');**  
**define('STORAGE_OPTIONS', 'myfile');**

And call login.php  

That's all !  

## Licence :

Q.uote.me is distributed under the zlib/libpng License :

Copyright (c) 2013 Daniel Douat, [Aélys Informatique](http://aelys-info.fr)

This software is provided 'as-is', without any express or implied warranty. In no event will the authors be held liable for any damages arising from the use of this software.  

Permission is granted to anyone to use this software for any purpose, including commercial applications, and to alter it and redistribute it freely, subject to the following restrictions :  

1. The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment in the product documentation would be appreciated but is not required.  

2. Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.  

3. This notice may not be removed or altered from any source distribution.

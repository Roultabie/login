#Login

*A simple script to create resctricted areas in your projects*

##How to use :

Download login and login form in your script dir, create a global config users like this :  
**$GLOBALS['config']['users']['john'] = array('hash' => '0e8aebd2ad0b1bd2cb49eeede3a56a9b42fe0a93', 'salt' => '34ebcf4e7f7b36523a1459cc6a67fd62f1ebc22f');**  
_Username john, password doe_

Use uwerWriter::generateUser() to generate your user:  
_print_r(userWriter::generateUser('john', 'doe', 'sha1'));  
And copy this informations in your config file.  
**SHA1 is the default hash method, make sure that you use the same method in userWriter __construc(). 
 
That's all !

##Licence :

Q.uote.me is distributed under the zlib/libpng License :

Copyright (c) 2013 Daniel Douat, [Aélys Informatique](http://aelys-info.fr)

This software is provided 'as-is', without any express or implied warranty. In no event will the authors be held liable for any damages arising from the use of this software.  

Permission is granted to anyone to use this software for any purpose, including commercial applications, and to alter it and redistribute it freely, subject to the following restrictions :  

1. The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment in the product documentation would be appreciated but is not required.  

2. Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.  

3. This notice may not be removed or altered from any source distribution.

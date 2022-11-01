# Elu

Elu is a Laravel-powered file (image) uploader that leaverages on the existing strength of Laravel's strong validation to add power to an exisitng web app. One of the additional benefits include enforcing (or constraining) image dimension. For instance, imagine your application expects image dimension to be 300/300px (height/width) and a user uploads an image with a dimension of 500/200px. Strictly constraining the dimension (using traditional approach) may result in low quality and a not-too-nice image. This library takes care of this effectively without sacrificing quality of the uploaded image.

## Installation

To get started all you need to do is:

```php
composer require sirmekus/elu
```

Once installed you can decide to publish the package's `elu` config file which you can then customise to your taste by running:

```php
php artisan vendor:publish --provider="Laravel\Elu\EluServiceProvider"
```

>Please note that this package stores uploaded files/images in the storage folder and expects that symbolic link must have already been created. If you haven't, no problem. Simply run:

```php
php artisan storage:link
```

That's all.

>Elu, in Igbo language, means **"Up"**; I bet you know how the name relates to this package now...#winks.

---

## Usage

---

## Receiving/Accepting file/image from client

Example:

```php
namespace App\Http\Controllers;

use Laravel\Elu\Elu;

class FileController extends Controller
{
    public function upload(Elu $elu)
    {
        //file check and validation if you wish

        $image = $elu->upload();
    }
}
```

Just that single line of code is what is needed to use this package. The package assumes the name (parameter) of the incoming file from the client is `image`. If it isn't then you can specify the name as the first argument. Example:

```html
<input type="file" accept="image/*" name='photo' />
```

```php
namespace App\Http\Controllers;

use Laravel\Elu\Elu;

class FileController extends Controller
{
    public function upload(Elu $elu)
    {
        //file check and validation if you wish

        $image = $elu->upload('photo');
    }
}
```

It doesn't matter if the file is an array or a single file, the package takes care of that, stores the uploaded file in disk and returns the new file name. If an array was detected the returned value will be an array containing the uploaded file names else a string that contains the file which you will want to save in the database.

You can set the configuration during runtime (if you choose to not use the configuration settings at any point in time) or overwrite the public properties of the class. The properties are:

- `docParentFolder` : This is typically the public directory in the storage folder. If you want the file to be saved outside this directory then specify it in the configuration file or overwrite this property.

- `sub_folder` : In the `docParentFolder` folder if there is another folder there where you'll like the image to be uploaded you can specify it here. You can specify it as like a file path (`subfolder/path/folder`) or just a single entry like (`subfolder`).

- `width` : The allowed width for this image.

- `height` : The allowed height for this image.

- `valid_mimes` : The allowed mime types for this image.

- `max_file_upload_size` : The max allowed size for this image in bytes.

- `max_no_of_file_to_upload` : The maximum number of file that can be uploaded for this image. This is useful if your users can upload multiple images.

- `name_of_file` : If specified, instead of randomly generated names this particular name will be used to rename the file. Be careful when setting this cause in a case of multiple file upload the files may be overwritten.

By default the `upload()` method checks if the specified key exists in the incoming request and is filled. If it is not it throws an error. In certain cases image upload may be optional, thus the application should still continue with the request processing. To enable this the method takes a second argument that suppresses the error. Example

```php
namespace App\Http\Controllers;

use Laravel\Elu\Elu;

class FileController extends Controller
{
    public function upload(Elu $elu)
    {
        //file check and validation if you wish

        $image = $elu->upload('photo', true);
    }
}
```

or, if you use use PHP 8.0 and above and the parameter name from the request is `image`.

```php
namespace App\Http\Controllers;

use Laravel\Elu\Elu;

class FileController extends Controller
{
    public function upload(Elu $elu)
    {
        //file check and validation if you wish

        $image = $elu->upload(ignore:true);
    }
}
```

## Displaying the image

Because the file is stored in the storage directory and a symbolic link has already been created you can simply do (in your blade file):

```php
{{ asset('storage/'.$user->image)}}
```

Or, if the image is saved in a sub-directory (in storage/public/):

```php
{{ asset('storage/sub_directory/'.$user->image)}}
```

## Deleting Image

To delete an image it follows the same logic and configuration as if uploading the image. Simply pass the image name(s) - string or array - to the `remove()` method and the job is done. Example:

```php
namespace App\Http\Controllers;

use Laravel\Elu\Elu;

class FileController extends Controller
{
    public function upload(Elu $elu)
    {
        //file check and validation if you wish

        $elu->remove($image);
    }
}
```

## Meanwhile

 You can connect with me on [LinkedIn](https://www.linkedin.com/in/sirmekus) for insightful tips and so we can grow our networks together.

 Patronise us on [Webloit](https://www.webloit.com).

 And follow me on [Twitter](https://www.twitter.com/Sire_Mekus).

 I encourage contribution even if it's in the documentation. Thank you, and I really hope you find this package helpful.

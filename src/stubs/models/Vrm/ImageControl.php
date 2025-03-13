<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Upload Image
use Intervention\Image\ImageManager as Image;
// use Intervention\Image\ImageManagerStatic as Image;
//use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageControl extends Model
{
    use HasFactory;

    /**
     * Todo: Images File
     *
     * ? This method is used to intiate the image uploading
     * ? This will be used in the controller
     * ? When passing files, also pass upload path, allow year/date folder to be created or not, lastest randomize file name
     * ? This will return the file upload path
     * ? State if is private upload or not (Bolean)
     * ? If is private, upload will be done in the storage folder NB: if the file will be accessed via http, it will not be accessible
     * ? Download will be possible (good for pdfs & receipts)
     *
     * @param array $files
     * @param string $upload_path
     * @param boolean $year_folder
     * @param boolean $randomize_file_name
     * @param boolean $private_upload = false
     * @param string $convert = null
     *
     * @return array
     */
    public static function uploadFile(array $files, string $upload_path = null, bool $year_folder = true, bool $randomize_file_name = true, bool $private_upload = false)
    {

        // Year/Month/Date
        $auto_folder = date('Y') . '/' . date('m') . '/' . date('d');

        // Uploaded
        $uploaded = [];

        // Generate a random name for each uploaded file
        foreach ($files as $file) {
            $file_name = ($randomize_file_name) ? uniqid() . '.' . $file->getClientOriginalExtension() : $file->getClientOriginalName();

            // Create the directory if it doesn't exist
            $directory = (!is_null($upload_path)) ? 'public/media-private/' . $upload_path : 'public/media-private';

            // Year/Month/Date
            if ($year_folder) {
                $directory = $directory . '/' . $auto_folder;
            }

            // Generate Folder if it doesn't exist
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Call to check if the file exists if it does, give it a new name
            $image_path_name = self::existImage($directory . '/' . $file_name, true); // True = yes give new name if current exist
            // Get the value after the last slash
            $new_file_name = substr($image_path_name, strrpos($image_path_name, '/') + 1);
            // Upload the file to the directory
            $file->storeAs($directory, $new_file_name);

            // Get the URL path for the uploaded file
            $url_path = Storage::url($directory . '/' . $new_file_name);

            // For Public Upload
            if ($private_upload == false) {
                // Create the directory if it doesn't exist
                $public_directory = (!is_null($upload_path)) ? 'media/' . $upload_path : 'media';

                // Year/Month/Date
                if ($year_folder) {
                    $public_directory = $public_directory . '/' . $auto_folder;
                }

                // Generate Folder if it doesn't exist
                if (!File::exists($public_directory)) {
                    File::makeDirectory($public_directory, 0755, true);
                }

                // Call to check if the image exists
                $image_path_name = self::existImage("$public_directory/$new_file_name", true); // True = yes give new name if current exist
                // Get the value after the last slash
                $public_file_name = substr($image_path_name, strrpos($image_path_name, '/') + 1);
                // Move To Public Directory
                $file->move($public_directory, $public_file_name);

                // Delete from the storage directory
                Storage::delete($directory . '/' . $new_file_name);

                // Get the URL path for the uploaded file
                $url_path = $public_directory . '/' . $public_file_name;
            }

            // Images
            $uploaded[] = $url_path;
        }

        // Reset array keys,
        $uploaded = array_values($uploaded);

        //  and if is empty return blank array
        if (count($uploaded) == 0) {
            return [];
        }

        // Return array
        return $uploaded;
    }

    /**
     * Todo: Upload From URL
     * ? This method is used to upload images from URL
     * ? This will be used in the controller
     * ? When passing files, also pass upload path, allow year/date folder to be created or not, lastest randomize file name
     * ? This will return the file upload path
     * ? State if is private upload or not (Bolean)
     * ? If is private, upload will be done in the storage folder NB: if the file will be accessed via http, it will not be accessible
     * ? Download will be possible (good for pdfs & receipts)
     *
     * @param array $urls
     * @param string $upload_path
     * @param string $image_name
     * @param string $extension
     * @param boolean $year_folder
     * @param boolean $private_upload = false
     *
     * @return array
     */
    public static function uploadImageFromUrl(array $urls, string $upload_path = null, string $image_name = null, string $extension = 'jpg', bool $year_folder = true, bool $private_upload = false): array
    {
        // Year/Month/Date
        $auto_folder = date('Y') . '/' . date('m') . '/' . date('d');

        // Uploaded
        $uploaded = [];

        // Generate a random name for each uploaded file
        foreach ($urls as $url) {
            // Get the image data
            $image_data = file_get_contents($url);

            // Image Name
            $image_name = (!is_null($image_name)) ? $image_name : uniqid();
            // Image with Extension
            $image_name = $image_name . '.' . $extension;

            // Create the directory if it doesn't exist
            $directory = (!is_null($upload_path)) ? 'media/' . $upload_path : 'media';

            // Check if is private upload
            if ($private_upload) {
                $directory = (!is_null($upload_path)) ? 'media-private/' . $upload_path : 'media-private';
            }

            // Year/Month/Date
            if ($year_folder) {
                $directory = $directory . '/' . $auto_folder;
            }

            // Get the URL path for the uploaded file
            $destination = public_path("$directory");
            // Default Path
            $default_path = $directory . '/' . $image_name;

            // Check if is private upload
            if ($private_upload) {
                $destination = storage_path("app/public/$directory"); // Destination folder and filename for the uploaded image
                $default_path = "storage/$directory/$image_name";
            }

            // Create the directory if it does not exist
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            // URL Path
            $url_path = "$destination/$image_name";

            // Save the image data to the specified destination
            file_put_contents($url_path, $image_data);

            // Images
            $uploaded[] = $default_path;
        }

        // Reset array keys,
        $uploaded = array_values($uploaded);

        //  and if is empty return blank array
        if (count($uploaded) < 1) {
            return [];
        }

        // Return array
        return $uploaded;
    }

    /**
     * Todo: Images Uploading
     *
     * ? This method is used to intiate the image uploading
     * ? This will be used in the controller
     * ? When passing images, also pass upload path, allow year/date folder to be created or not, lastest randomize image file name
     * ? This will return the image upload path
     * ? State if is private upload or not (Bolean)
     * ? If is private, upload will be done in the storage folder NB: if the file will be accessed via http, it will not be accessible
     * ? Download will be possible (good for pdfs & receipts)
     *
     * @param array $images
     * @param string $upload_path
     * @param boolean $year_folder
     * @param boolean $randomize_file_name
     * @param boolean $private_upload = false
     * @param string $convert = null,webp,png,jpg
     *
     * @return array
     */
    public static function uploadImage(array $images, string $upload_path = null, bool $year_folder = true, bool $randomize_file_name = true, bool $private_upload = false, string $convert = 'webp'): array
    {

        // Upload Files First
        $uploaded = self::uploadFile($images, $upload_path, $year_folder, $randomize_file_name, $private_upload);

        // Convert to webp
        if (count($uploaded) > 0) {
            if (!is_null($convert)) {
                foreach ($uploaded as $key => $image) {
                    $uploaded[$key] = self::convertImage($image, $convert);
                }
            }
        }

        // Return array
        return $uploaded;
    }

    /**
     * Todo: Generate Thumbnail
     * ? This will be used to add thumbnail of images
     * ? Pass the image path
     * ? Pass the width and height (in pixels)
     * ? Pass Maintain Ratio (true or false)
     * ? Pass add background (true or false)
     * ? Pass background color
     * ? By default the thumbnail will be created as _thumb
     * ? Override allow to override the original _thumb if is false then new thumbnail will be created as _thumb_1 and so on
     * ? Override the original image (true or false)
     *
     * ? Utillize the resizeImage() method
     *
     * @param string $image_path
     * @param integer $width
     * @param integer $height
     * @param boolean $maintain_ratio
     * @param boolean $add_background
     * @param string $background_color
     * @param boolean $override default true
     *
     * @return string image_path
     */
    public static function thumbnailImage(string $image_path, int $width = 72, int $height = 72, bool $maintain_ratio = true, bool $add_background = true, string $background_color = '#041419', bool $override = true): string
    {

        // Resize the image
        $image_path = self::resizeImage($image_path, $width, $height, $maintain_ratio, $add_background, $background_color, false); //Resize but don't override

        // Check if is null or empty
        if (is_null($image_path) || empty($image_path)) {
            return '';
        }

        // Store the image path
        $image_passed_path = $image_path;
        // Get the image directory
        $image_passed_directory = pathinfo($image_passed_path, PATHINFO_DIRNAME);

        /**
         * Thumbnail the image
         */
        // Get Dir Path
        $image_path = self::clearPath($image_path);

        // Resized Image
        $image_path_resized = $image_path;
        $manager = new Image(new Driver());

        // Get the image
        // $image = Image::imagick()->read($image_path);
        $image = $manager->read($image_path);

        // Get the Current image extension
        $extension = pathinfo($image_path, PATHINFO_EXTENSION);
        // Get the image name
        $image_name = pathinfo($image_path, PATHINFO_FILENAME);
        // Replace _resized
        $image_name = str_replace('_resized', '', $image_name);
        // Get the image directory
        $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);
        // New Image Name
        $image_file_name = $image_name . '_thumb.' . $extension;

        // Create the new image path
        $image_path = $image_directory . '/' . $image_file_name;

        // Override the original image
        if ($override == false) {
            // New Image Name
            $image_file_name = $image_name . '.' . $extension;
            // Call to check if the image exists
            $image_path = self::existImage($image_path, true); // True = yes give new name if current exist

            // Get the Current image extension
            $extension = pathinfo($image_path, PATHINFO_EXTENSION);
            // Get the image name
            $image_name = pathinfo($image_path, PATHINFO_FILENAME);
            // New Image Name
            $image_file_name = $image_name . '.' . $extension;
        }
        // Remove the original image
        unlink($image_path_resized);

        // Save the image
        $image->save($image_path);
        /**
         * End Thumbnail the image
         */

        // Default Path
        $default_path = $image_passed_directory . '/' . $image_file_name;

        // Return the image path
        return $default_path;
    }

    /**
     * Todo : Resize Images
     * ? This will be used to resize images
     * ? Pass the image path
     * ? Pass the width and height (in pixels)
     * ? Pass Maintain Ratio (true or false)
     * ? Pass add background (true or false)
     * ? Pass background color
     *
     * @param string $image_path
     * @param integer $width
     * @param integer $height
     * @param boolean $maintain_ratio
     * @param boolean $add_background
     * @param string $background_color
     * @param boolean $override default true
     *
     * @return string image_path
     */
    public static function resizeImage(string $image_path, int $width, int $height, bool $maintain_ratio = true, bool $add_background = false, string $background_color = '#041419', bool $override = true): string
    {
        // Check If the image exists
        if (self::existImage($image_path) == false) {
            return '';
        }

        // Store the image path
        $image_passed_path = $image_path;
        // Get the image directory
        $image_passed_directory = pathinfo($image_passed_path, PATHINFO_DIRNAME);

        /**
         * Resize the image
         */

        // Get Dir Path
        $image_path = self::clearPath($image_path);
        $manager = new Image(new Driver());

        // Get the image
        // $image = Image::imagick()->read($image_path);
        $image = $manager->read($image_path);

        // Resize the image
        $image->resize($width, $height, function ($constraint) use ($maintain_ratio) {
            if ($maintain_ratio) {
                $constraint->aspectRatio();
            }
        });

        // Add background
        if ($add_background) {
            $image->resizeCanvas($width, $height, $background_color, 'center');
        }

        // Get image extension
        $extension = pathinfo($image_path, PATHINFO_EXTENSION);
        // Get the image name
        $image_name = pathinfo($image_path, PATHINFO_FILENAME);

        // New Image Name
        $image_file_name = $image_name . '.' . $extension;

        // Save the image as the _resized version
        if ($override == false) {
            // New Image Name
            $image_file_name = $image_name . '_resized.' . $extension;
            // Get the image directory
            $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        }

        // Save the image
        $image->save($image_path);
        /**
         * End Resize the image
         */

        // Default Path
        $default_path = $image_passed_directory . '/' . $image_file_name;

        // Return the image path
        return $default_path;
    }

    /**
     * Todo: Compress Images
     * ? This will be used to compress images
     * ? Pass the image path
     * ? Pass the convert type (webp, png, jpg, etc)
     * ? Pass the quality (0 - 100)
     * ? Override the original image (true or false)
     *
     * @param string $image_path
     * @param string $convert
     * @param integer $quality
     * @param boolean $override
     *
     * @return string image_path
     */
    public static function compressImage(string $image_path, string $convert = null, int $quality = 100, bool $override = true): string
    {
        // Check If the image exists
        if (self::existImage($image_path) == false) {
            return '';
        }

        // Store the image path
        $image_passed_path = $image_path;
        // Get the image directory
        $image_passed_directory = pathinfo($image_passed_path, PATHINFO_DIRNAME);

        /**
         * Compress the image
         */

        // Get Dir Path
        $image_path = self::clearPath($image_path);
        $manager = new Image(new Driver());

        // Get the image
        // $image = Image::imagick()->read($image_path);
        $image = $manager->read($image_path);

        // Convert the image
        if (!is_null($convert)) {
            $image->encodeByMediaType("image/$convert", $quality);
        } else {
            $ext = pathinfo($image_path, PATHINFO_EXTENSION);
            $image->encodeByMediaType("image/$ext", $quality);
        }

        // Get the Current image extension
        $extension = (!is_null($convert)) ? $convert :  pathinfo($image_path, PATHINFO_EXTENSION);
        // Get the image name
        $image_name = pathinfo($image_path, PATHINFO_FILENAME);
        // Get the image directory
        $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);
        // Save the image as the _compressed version
        if ($override == false) {
            // New Image Name
            $image_file_name = $image_name . '_compressed.' . $extension;
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        } else {
            // Remove the original image
            unlink($image_path);
            // New Image Name
            $image_file_name = $image_name . '.' . $extension;
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        }

        // Save the image
        $image->save($image_path);
        /**
         * End Compress the image
         */

        // Default Path
        $default_path = $image_passed_directory . '/' . $image_file_name;

        // Return the image path
        return $default_path;
    }

    /**
     * Todo: Convert Images
     * ? This will be used to convert images
     * ? Pass the image path
     * ? Pass the convert type (webp, png, jpg, etc)
     * ? Pass the quality (0 - 100)
     * ? Pass the override (true or false)
     *
     * @param string $image_path
     * @param string $convert
     * @param integer $quality
     * @param boolean $override
     *
     * @return string image_path
     */
    public static function convertImage(string $image_path, string $convert, int $quality = 100, bool $override = true): string
    {
        // Check If the image exists
        if (self::existImage($image_path) == false) {
            return '';
        }

        // Store the image path
        $image_passed_path = $image_path;
        // Get the image directory
        $image_passed_directory = pathinfo($image_passed_path, PATHINFO_DIRNAME);

        /**
         * Convert the image
         */

        // Get Dir Path
        $image_path = self::clearPath($image_path);
        $manager = new Image(new Driver());

        // Get the image
        // $image = Image::imagick()->read($image_path);
        $image = $manager->read($image_path);

        $image->encodeByMediaType("image/$convert", $quality);

        // Get the Current image extension
        $extension = strtolower(trim($convert));
        // Get the image name
        $image_name = pathinfo($image_path, PATHINFO_FILENAME);
        // Get the image directory
        $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);

        // Save the image as the _converted version
        if ($override == false) {
            // New Image Name
            $image_file_name = $image_name . '_converted.' . $extension;
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        } else {
            // Remove the original image
            unlink($image_path);
            // New Image Name
            $image_file_name = $image_name . '.' . $extension;
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        }

        // Save the image
        $image->save($image_path);
        /**
         * End Convert the image
         */

        // Default Path
        $default_path = $image_passed_directory . '/' . $image_file_name;

        // Return the image path
        return $default_path;
    }

    /**
     * Todo: Add Watermark
     * ? This will be used to add watermark to images
     * ? Pass the image path
     * ? Pass the watermark image path
     * ? Pass the position (top-left, top-right, bottom-left, bottom-right, center)
     * ? Pass the opacity (0 - 100)
     * ? Pass the x-axis offset
     * ? Pass the y-axis offset
     * ? Override the original image (true or false)
     *
     * @param string $image_path
     * @param string $watermark_image_path
     * @param string $position default center (top-left, top-right, bottom-left, bottom-right, center)
     * @param integer $opacity default 50
     * @param integer $x_offset default 10
     * @param integer $y_offset default 10
     * @param boolean $override default false
     *
     * @return string image_path
     */
    public static function watermarkImage(string $image_path, string $watermark_image_path, string $position = 'center', int $opacity = 50, int $x = 10, int $y = 10, bool $override = true)
    {
        // Check If the image exists
        if (self::existImage($image_path) == false) {
            return '';
        }

        // Check If the watermark image exists
        if (self::existImage($watermark_image_path) == false) {
            return '';
        }

        // Store the image path
        $image_passed_path = $image_path;
        // Get the image directory
        $image_passed_directory = pathinfo($image_passed_path, PATHINFO_DIRNAME);

        /**
         * Add the watermark
         */

        // Get Dir Path
        $image_path = self::clearPath($image_path);
        $manager = new Image(new Driver());

        // Get the image
        // $image = Image::imagick()->read($image_path);
        $image = $manager->read($image_path);

        // Get the watermark image
        // $watermark_image = Image::imagick()->read($watermark_image_path);
        $watermark_image = $manager->read($watermark_image_path);

        // Add the watermark
        $image->place($watermark_image, $position, $x, $y);

        // Get image extension
        $extension = pathinfo($image_path, PATHINFO_EXTENSION);

        // Get the image name
        $image_name = pathinfo($image_path, PATHINFO_FILENAME);
        // Get the image directory
        $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);

        // image path
        $image_path = $image_directory . '/' . $image_name . '.' . $extension;

        // New Image Name
        $image_file_name = $image_name . '.' . $extension;

        // Save the image as the _watermark version
        if ($override == false) {
            // New Image Name
            $image_file_name = $image_name . '_watermark.' . $extension;
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        }

        // Save the image
        $image->save($image_path);
        /**
         * End Add the watermark
         */

        // Default Path
        $default_path = $image_passed_directory . '/' . $image_file_name;

        // Return the image path
        return $default_path;
    }

    /**
     * Todo: Add Text Watermark
     * ? This will be used to add text watermark to images
     * ? Pass the image path
     * ? Pass Watermark text
     * ? Pass the position - align (left, right, center)
     * ? Pass the position - [valign] vertical (top, bottom, center)
     * ? Pass the font size
     * ? Pass the font color
     * ? Pass the opacity (0 - 100)
     * ? Pass the x-axis offset
     * ? Pass the y-axis offset
     * ? Override the original image (true or false)
     *
     * @param string $image_path
     * @param string $text
     * @param string $align default center (left, right, center)
     * @param string $valign default center (top, bottom, center)
     * @param string $font_family default null (1: gdFontTiny, 2: gdFontSmall, 3: gdFontMediumBold, 4: gdFontLarge, 5: gdFontGiant, or path to font)
     * @param integer $font_size default 12
     * @param any $font_color default RGB Color array(4, 20, 26) (R, G, B) | or use #000000 but this will not work with opacity
     * @param integer $opacity default 50
     * @param integer $x_offset default 10
     * @param integer $y_offset default 10
     * @param boolean $override default false
     *
     * @return string image_path
     */
    public static function textWatermarkImage(string $image_path, string $text, string $align = 'center', string $valign = 'center', $font_family = 3, int $font_size = 50, $font_color = [4, 20, 26], int $opacity = 50, int $x = 50, int $y = 20, bool $override = true)
    {
        // Check If the image exists
        if (self::existImage($image_path) == false) {
            return '';
        }

        // Store the image path
        $image_passed_path = $image_path;
        // Get the image directory
        $image_passed_directory = pathinfo($image_passed_path, PATHINFO_DIRNAME);

        /**
         * Add Text watermark
         */

        // Get Dir Path
        $image_path = self::clearPath($image_path);

        // Get the image
        $manager = new Image(new Driver());
        $image = $manager->read($image_path);

        // Add the watermark
        $image->text($text, $x, $y, function ($font) use ($align, $valign, $font_family, $font_size, $font_color, $opacity) {
            if (!is_int($font_family)) {
                if (file_exists($font_family) == false) {
                    return '';
                }
                // Assign Font
                $font->file($font_family);
            } else {
                $font->file($font_family);
            }
            // Assign Font Color
            if (is_array($font_color) && count($font_color) == 3) {
                $font->color([
                    $font_color[0],
                    $font_color[1],
                    $font_color[2],
                    round((1 - $opacity) * 127.5)
                ]);
            } else {
                $font->color($font_color);
            }
            // Assign Font
            $font->size($font_size);
            $font->align($align);
            $font->valign($valign);
        });

        // Get image extension
        $extension = pathinfo($image_path, PATHINFO_EXTENSION);

        // Get the image name
        $image_name = pathinfo($image_path, PATHINFO_FILENAME);
        // Get the image directory
        $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);

        // image path
        $image_path = $image_directory . '/' . $image_name . '.' . $extension;

        // New Image Name
        $image_file_name = $image_name . '.' . $extension;

        // Save the image as the _textwatermark version
        if ($override == false) {
            // New Image Name
            $image_file_name = $image_name . '_textwatermark.' . $extension;
            // Create the new image path
            $image_path = $image_directory . '/' . $image_file_name;
        }

        // Save the image
        $image->save($image_path);

        /**
         * End Add Text watermark
         */

        // Default Path
        $default_path = $image_passed_directory . '/' . $image_file_name;

        // Return the image path
        return $default_path;
    }

    /**
     * Todo: Method to check if Image exist
     * ? Pass the image path
     * ? Allow generating new image name if current exist
     * ? Numerate the image name start from 1x
     *
     * @param string $image_path
     * @param boolean $generate_new_name
     * @param integer $numeration
     *
     */
    public static function existImage(string $image_path, $generate_new_name = false, $numeration = 1)
    {

        // Exist
        $exist = false;

        // Check if string start with storage/
        if (Str::startsWith($image_path, 'storage/')) {
            $image_path = Str::replaceFirst('storage/', '/storage/', $image_path);
        }

        // If the first path has storage/ remove it, if it's later in the path, keep it
        if (Str::startsWith($image_path, '/storage/')) {
            // If there is /storage/public/ in the path, remove it
            if (Str::startsWith($image_path, '/storage/public/')) {
                $image_path = Str::replaceFirst('/storage/public/', 'public/', $image_path);
            } else {
                $image_path = Str::replaceFirst('/storage/', 'public/', $image_path);
            }
        }

        // Exist in public
        if (File::exists($image_path)) {
            $exist = true;
        } elseif (Storage::exists($image_path)) {
            $exist = true;
        }

        // Check if the image exist
        if ($exist == true) {
            // Generate new name
            if ($generate_new_name) {
                // Get the Current image extension
                $extension = pathinfo($image_path, PATHINFO_EXTENSION);
                // Get the image name
                $image_name = pathinfo($image_path, PATHINFO_FILENAME);
                // Get the image directory
                $image_directory = pathinfo($image_path, PATHINFO_DIRNAME);
                // Check if the image name has _thumb
                if (strpos($image_name, '_thumb') !== false) {
                    // Replace everything after _thumb but keep the _thumb
                    $image_name = explode('_thumb', $image_name)[0];
                    // Add the _thumb back
                    $image_name = $image_name . '_thumb';
                }

                // Create the new image path
                $image_path = $image_directory . '/' . $image_name . '_' . $numeration . 'x.' . $extension;
                // Call the method again
                return self::existImage($image_path, true, $numeration + 1);
                // Return the image path
            }
            // Return true
            return true;
        }
        return ($generate_new_name) ? $image_path : false;
    }

    /**
     * Todo: Method Clear Path
     * ? Pass the path
     *
     * @param string $image_path
     *
     * @return string $image_path
     */
    public static function clearPath(string $image_path): string
    {

        // Check if string start with storage/
        if (Str::startsWith($image_path, 'storage/')) {
            $image_path = Str::replaceFirst('storage/', '/storage/', $image_path);
        }

        // If the first path has storage/ remove it, if it's later in the path, keep it
        if (Str::startsWith($image_path, '/storage/')) {
            // If there is /storage/public/ in the path, remove it
            if (Str::startsWith($image_path, '/storage/public/')) {
                $image_path = Str::replaceFirst('/storage/public/', 'app/public/', $image_path);
            } else {
                $image_path = Str::replaceFirst('/storage/', 'app/public/', $image_path);
            }

            // Storage Path
            $path_to_image = storage_path($image_path);
        } else {
            // Public Path
            $path_to_image = public_path($image_path);
        }

        // Return the image path
        return $path_to_image;
    }

    /**
     * Todo: Convert File
     *
     * ? This method is used to convert File Format
     * ? This will be used in the controller
     * ? When passing files, also pass upload path, allow year/date folder to be created or not, lastest randomize file name
     * ? This will return the file upload path
     * ? State if is private upload or not (Bolean)
     * ? If is private, upload will be done in the storage folder NB: if the file will be accessed via http, it will not be accessible
     * ? Download will be possible (good for pdfs & receipts)
     *
     * @param array $files
     * @param string $upload_path
     * @param boolean $year_folder
     * @param boolean $randomize_file_name
     * @param boolean $private_upload = false
     * @param string $convert = null
     *
     * @return array
     */
    public static function convertFile(array $files, string $upload_path = null, bool $year_folder = true, bool $randomize_file_name = true, bool $private_upload = false, $convert = 'pdf')
    {

        // Year/Month/Date
        $auto_folder = date('Y') . '/' . date('m') . '/' . date('d');

        // Uploaded
        $uploaded = [];

        // Generate a random name for each uploaded file
        foreach ($files as $file) {
            $file_name = ($randomize_file_name) ? uniqid() . '.' . $convert : $file->getClientOriginalName();

            // Create the directory if it doesn't exist
            $directory = (!is_null($upload_path)) ? 'public/media-private/' . $upload_path : 'public/media-private';

            // Year/Month/Date
            if ($year_folder) {
                $directory = $directory . '/' . $auto_folder;
            }

            // Generate Folder if it doesn't exist
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Call to check if the file exists if it does, give it a new name
            $image_path_name = self::existImage($directory . '/' . $file_name, true); // True = yes give new name if current exist
            // Get the value after the last slash
            $new_file_name = substr($image_path_name, strrpos($image_path_name, '/') + 1);
            // Upload the file to the directory
            $file->storeAs($directory, $new_file_name);

            // Get the URL path for the uploaded file
            $url_path = Storage::url($directory . '/' . $new_file_name);

            // For Public Upload
            if ($private_upload == false) {
                // Create the directory if it doesn't exist
                $public_directory = (!is_null($upload_path)) ? 'media/' . $upload_path : 'media';

                // Year/Month/Date
                if ($year_folder) {
                    $public_directory = $public_directory . '/' . $auto_folder;
                }

                // Generate Folder if it doesn't exist
                if (!File::exists($public_directory)) {
                    File::makeDirectory($public_directory, 0755, true);
                }

                // Call to check if the image exists
                $image_path_name = self::existImage("$public_directory/$new_file_name", true); // True = yes give new name if current exist
                // Get the value after the last slash
                $public_file_name = substr($image_path_name, strrpos($image_path_name, '/') + 1);
                // Move To Public Directory
                $file->move($public_directory, $public_file_name);

                // Delete from the storage directory
                Storage::delete($directory . '/' . $new_file_name);

                // Get the URL path for the uploaded file
                $url_path = $public_directory . '/' . $public_file_name;
            }

            // Use LibreOffice for DOCX to PDF conversion
            $command = "libreoffice --headless --convert-to pdf --outdir " . escapeshellarg($directory) . " " . escapeshellarg($url_path);
            exec($command);

            // Images
            $uploaded[] = $url_path;
        }

        // Reset array keys,
        $uploaded = array_values($uploaded);

        //  and if is empty return blank array
        if (count($uploaded) == 0) {
            return [];
        }

        // Return array
        return $uploaded;
    }

    /**
     * Todo: Method Get File Extension from passed url/path/name
     * ? Pass the path
     *
     * @param string $file_path
     */
    public static function getFormat($file_path)
    {
        // Parse the URL to get the path part
        $path = parse_url($file_path, PHP_URL_PATH);

        // Use pathinfo to get the extension
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension;
    }

    /**
     * Todo: Method Get File Name from passed url/path/name
     * ? Pass the path
     *
     * @param string $file_path
     */
    public static function getName($file_path)
    {
        // Parse the URL to get the path part
        $path = parse_url($file_path, PHP_URL_PATH);

        // Use pathinfo to get the filename without the extension
        $filename = pathinfo($path, PATHINFO_FILENAME);

        return $filename;
    }
}

<?php

namespace App\Models;

use App\Models\Vrm\Term;
use App\Models\Vrm\ImageControl;

// Upload Image
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Auto extends Model
{
    use HasFactory;

    /**
     *
     * To load libraries/Model/Helpers/Add custom code which will be used in this Model
     * This can ease the loading work
     *
     */
    public function __construct()
    {

        parent::__construct();

        //libraries

        //Helpers

        //Models

        // Your own constructor code
    }

    /**
     *
     * Todo:: This function is used to load all your public data
     * ? This will be loaded globally
     */
    public function loadData()
    {
        $data['breadcrumb'] = [];
        $data['brand_name'] = "Vormia";
        $data['pagetitle'] = '';
        $data['main_website'] = '';
        $data['base_url'] = url('/');
        $data['api_url'] = '';
        $data['this_page'] = '';

        $data['allow_payment'] = true;

        $data['curr_menu'] = '';
        // Return
        return $data;
    }

    /*---------------- CURL POST ---------------------*/
    /**
     * Encoding the data For GET
     *
     * Pass Value as Array/String
     */
    private function encord_parameter($values)
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $values[$key] = urlencode($value);
            }
        } else {
            $values = urlencode($values);
        }
        return $values;
    }

    /**
     * Todo: Uploading Files
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
     */
    public static function uploadFile(array $files, string $upload_path = null, bool $year_folder = true, bool $randomize_file_name = true, bool $private_upload = false)
    {
        // Uploaded
        $uploaded = ImageControl::uploadFile($files, $upload_path, $year_folder, $randomize_file_name, $private_upload);

        //  and if is null return null
        if (count($uploaded) == 0) {
            return null;
        }

        // Reset array keys,
        $uploaded = array_values($uploaded);

        // Return, check if there is only one image
        return (count($uploaded) == 1) ? $uploaded[0] : $uploaded;
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
     */
    public static function uploadImage(array $images, string $upload_path = null, bool $year_folder = true, bool $randomize_file_name = true, bool $private_upload = false, string $convert = 'webp')
    {

        // Uploaded
        $uploaded = ImageControl::uploadImage($images, $upload_path, $year_folder, $randomize_file_name, $private_upload, $convert);

        //  and if is null return null
        if (count($uploaded) == 0) {
            return null;
        }

        // Images
        $images = [];

        // Generate a random name for each uploaded file
        foreach ($uploaded as $img) {
            // Add Thumbnail 79 × 79 px
            $image_thumb = ImageControl::thumbnailImage($img, width: 50, height: 20, override: true);
            // resize image
            $image_resized = ImageControl::resizeImage($img, width: 400, height: 160, add_background: true);

            // ? Temporarily fix
            $image_resized = $img;

            // Images
            $images[] = $image_resized;
        }

        // Reset array keys,
        $images = array_values($images);

        // Return, check if there is only one image
        return (count($images) == 1) ? $images[0] : $images;
    }
}

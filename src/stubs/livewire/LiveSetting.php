<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Vrm\Notify;
use App\Models\Vrm\Setting;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class LiveSetting extends Component
{

    // PRIVATE VARIABLES
    private $MainFolder = ""; //Main Folder Name (in prural) inside the resources/views/$ThemePath/pages
    private $SubFolder = ""; //Sub Folder Name inside the resources/views/$ThemePath/pages/$MainFolder/
    private $Upload = ""; //Upload Folder Name inside the public/admin/media

    private $ParentRoute = ""; // Parent Route Name Eg. vrm-settings
    private $AllowedFile = null; //Set Default allowed file extension, remember you can pass this upon upload to override default allowed file type. jpg|jpeg|png|doc|docx|

    /**
     * Todo: All of your public variables for storing data will be here on top
     *
     * ? By default we have created $form_data for storing form data & $passed_data for storing passed data between components
     * ? You can add yours
     */
    public $notify;
    public $passed_data = [];
    public $form_data = [];

    // Single Input
    public $search = '';

    /**
     * Todo: Mounted Data
     *
     * ? This method is used to mount data to the component
     */
    public function mount()
    {
        // Mount previous data from session
        $this->passed_data = session('passed-data', []);

        // Notify
        $this->notify = '';
    }

    /**
     * Global Settings {loadSettings}
     * Method is private and not accessible via the web
     * Todo: This method Load all settings from database via the PreLoad Model:: getSettings()
     *
     * @param optional $view_name (string) Page Name (make sure to add $ThemePath/$MainFolder/$SubFolder/$page_name)
     *
     * @return \Illuminate\Http\Response
     */
    private function loadSettings($view_name = '')
    {
        // Load in Controller Settings from passedSettings method
        $passed = $this->passedSettings();

        //openLoad settings
        $settings = Setting::preLoad($view_name, $passed);

        // Return all settings
        return $settings;
    }

    /**
     * Custom Settings {passedSettings}
     * Method is private and not accessible via the web
     * Todo: This method Load all settings for this Controller only
     *
     * @param optional $addtionalData (array) any additional data to be passed on demand
     *
     * @return \Illuminate\Http\Response
     */
    private function passedSettings($addtional_data = [])
    {
        date_default_timezone_set('Africa/Nairobi'); //Time Zone
        $setting['dateTime'] = strtotime(date('Y-m-d, H:i:s')); //Current DateTime

        // Links
        $setting['links'] = (object)[
            'route' => $this->ParentRoute,
        ];

        // Other
        $setting['other'] = (object)[
            'headerName' => (!array_key_exists('headerName', $addtional_data)) ? '' : $addtional_data['headerName'],
        ];

        // Header
        $setting['h4_pagetitle'] = '';
        $setting['breadcrumb'] = [];

        // Merge all settings into one array
        $setting = array_merge($setting, $addtional_data);

        // Return all settings
        return $setting;
    }

    /**
     * Todo: Handle Notification
     * ? This method is used to handle notification
     *
     */
    #[On('livesetting-notify')]
    public function livesetting_notify(array $response = ['status' => null, 'message' => null])
    {

        // Values
        $_status = array_key_exists('status', $response) ? $response['status'] : null;
        $_message = array_key_exists('message', $response) ? $response['message'] : '';

        //Notification
        $notify = (is_null($_status)) ? Notify::notify() : $_status;
        $this->notify = Notify::$notify($_message);

        Log::info('Notify: ' . $this->notify);
    }

    /**
     * Method is used to render the view/page visible via browser.
     * Todo: This method is the only method that is accessible render the view/page visible via browser.
     */
    public function render()
    {

        // Page Path & Layout
        $view = 'setting';
        $layout_name = 'main';

        // Page
        $page = Str::plural($this->MainFolder) . $this->SubFolder .  "/$view";

        // Load Settings
        $data = $this->loadSettings($page);
        $data['other']->view = $view;

        //Notification
        $data['notify'] =  $this->notify;

        // This page & layout
        $_this_page = $data['theme_dir'] . "/pages/" . $data['page_name'];
        $_this_layout = $data['theme_dir'] . "/layouts/livewire/$layout_name";

        // Render
        return view($_this_page, $data)->layout($_this_layout, Setting::preLoad());
    }

    /* --------------------------------------------------------------------------------------------- */

    /**
     * Todo: Demo Listen to Input Event
     *
     * ? This method is used listen to an iput event
     * ? In this case we're listening to input for search
     *
     * ? We're listening using wire:model.live="search"
     */
    public function keydownSearch()
    {
        // Validate Data
        $validated = $this->validate([
            'search' => 'nullable|string',
        ]);

        // Add Data to Session
        $data['search'] = $validated['search'];
        session(['passed-data' => $data]);

        // Dispatch event to trigger results component
        $this->dispatch('search-initiated', $this->search);

        // Reset Edit
        $this->dispatch('reset-edit-initiated');
    }
}
